<?php
require_once 'config.php';
function getAvailableSeasons($dataDir = __DIR__ . '/data') {
    $files = glob($dataDir . '/*.json');
    return array_map(fn($file) => basename($file, '.json'), $files);
}

function loadMatchData($season = "all", $dataDir = __DIR__ . '/data') {
    if ($season === 'all') {
        $files = glob($dataDir . '/*.json');
    } elseif (is_array($season)) {
        $files = array_map(fn($s) => "$dataDir/$s.json", $season);
    } else {
        $file = "$dataDir/$season.json";
        if (!file_exists($file)) return [];
        $files = [$file];
    }

    $allData = [];
    foreach ($files as $file) {
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
            if (is_array($json)) {
                $allData = array_merge($allData, $json);
            }
        }
    }
    return $allData;
}

function calculateTeamStats($data, $team) {
    $matches = array_filter($data, fn($match) => $match['home_team'] === $team || $match['away_team'] === $team);
    $totalMatches = count($matches);
    if ($totalMatches === 0) return false;

    $goalsFor = $goalsAgainst = $yellowCards = $redCards = 0;

    foreach ($matches as $match) {
        if ($match['home_team'] === $team) {
            $goalsFor += $match['home_goals'];
            $goalsAgainst += $match['away_goals'];
            $yellowCards += $match['yellow_cards_home_team'];
            $redCards += $match['red_cards_home_team'];
        } else {
            $goalsFor += $match['away_goals'];
            $goalsAgainst += $match['home_goals'];
            $yellowCards += $match['yellow_cards_away_team'];
            $redCards += $match['red_cards_away_team'];
        }
    }

    return [
        'attack' => $goalsFor / $totalMatches,
        'defense' => $goalsAgainst / $totalMatches,
        'yellow_cards' => $yellowCards / $totalMatches,
        'red_cards' => $redCards / $totalMatches,
    ];
}

function getParameters() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT parameter_name, parameter_value FROM prediction_parameters");
    $stmt->execute();
    $params = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $params[$row['parameter_name']] = $row['parameter_value'];
    }
    return $params;
}

function adjustStrength($attack, $defense, $yellow, $red) {
    $params = getParameters();
    $penalty = 1 - ($params['yellow_card_penalty'] * $yellow + $params['red_card_penalty'] * $red);
    $penalty = max(0.6, $penalty);
    return [
        'attack' => max($params['min_strength'], $attack * $penalty),
        'defense' => max($params['min_strength'], $defense * $penalty),
    ];
}

function poisson($lambda, $k) {
    return (pow($lambda, $k) * exp(-$lambda)) / factorial($k);
}

function factorial($n) {
    if ($n <= 1) return 1;
    return $n * factorial($n - 1);
}

function predictScores($statsA, $statsB) {
    $params = getParameters();
    $teamA = adjustStrength($statsA['attack'], $statsA['defense'], $statsA['yellow_cards'], $statsA['red_cards']);
    $teamB = adjustStrength($statsB['attack'], $statsB['defense'], $statsB['yellow_cards'], $statsB['red_cards']);

    $lambdaA = $teamA['attack'] / $teamB['defense'] * $params['home_advantage'];
    $lambdaB = $teamB['attack'] / $teamA['defense'];

    $maxGoals = $params['max_goals'];
    $results = [];

    for ($i = 0; $i <= $maxGoals; $i++) {
        for ($j = 0; $j <= $maxGoals; $j++) {
            $prob = poisson($lambdaA, $i) * poisson($lambdaB, $j);
            $results["$i-$j"] = $prob;
        }
    }

    $winA = $draw = $winB = 0;
    foreach ($results as $score => $prob) {
        [$a, $b] = explode('-', $score);
        if ($a > $b) $winA += $prob;
        elseif ($a == $b) $draw += $prob;
        else $winB += $prob;
    }

    return [
        'winA' => round($winA * 100, 2),
        'draw' => round($draw * 100, 2),
        'winB' => round($winB * 100, 2),
        'details' => $results
    ];
}

function logPrediction($teamA, $teamB, $season, $prediction) {
    global $pdo;
    
    // Get current user from session
    $user = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    
    $stmt = $pdo->prepare("
        INSERT INTO prediction_history1
        (user, teamA, teamB, season, winA, draw, winB)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user,
        $teamA,
        $teamB,
        $season,
        $prediction['winA'],
        $prediction['draw'],
        $prediction['winB']
    ]);
}

function handlePrediction($teamA, $teamB, $season) {
    $matches = loadMatchData($season);
    $dataA = calculateTeamStats($matches, $teamA);
    $dataB = calculateTeamStats($matches, $teamB);

    if (!$dataA || !$dataB) {
        return ['error' => 'Invalid team names or no data found for selected season.'];
    }

    $prediction = predictScores($dataA, $dataB);
    
    // Log the prediction
    logPrediction($teamA, $teamB, $season, $prediction);
    
    return $prediction;
}

function evaluatePredictionAccuracy($teamA, $teamB, $season) {
    // Get the prediction without logging it
    $matches = loadMatchData($season);
    $dataA = calculateTeamStats($matches, $teamA);
    $dataB = calculateTeamStats($matches, $teamB);

    if (!$dataA || !$dataB) {
        return ['error' => 'Invalid team names or no data found for selected season.'];
    }

    $prediction = predictScores($dataA, $dataB);
    
    // Filter matches between these teams
    $actualMatches = array_filter($matches, function($match) use ($teamA, $teamB) {
        return ($match['home_team'] === $teamA && $match['away_team'] === $teamB) ||
               ($match['home_team'] === $teamB && $match['away_team'] === $teamA);
    });

    if (empty($actualMatches)) {
        return ['error' => 'No matches found between these teams in the selected season.'];
    }

    // Count actual results
    $actualWinA = $actualDraw = $actualWinB = 0;
    foreach ($actualMatches as $match) {
        if ($match['home_team'] === $teamA) {
            if ($match['home_goals'] > $match['away_goals']) $actualWinA++;
            elseif ($match['home_goals'] === $match['away_goals']) $actualDraw++;
            else $actualWinB++;
        } else {
            if ($match['away_goals'] > $match['home_goals']) $actualWinA++;
            elseif ($match['away_goals'] === $match['home_goals']) $actualDraw++;
            else $actualWinB++;
        }
    }

    // Calculate chi-square test
    $totalMatches = count($actualMatches);
    $expectedWinA = ($prediction['winA'] / 100) * $totalMatches;
    $expectedDraw = ($prediction['draw'] / 100) * $totalMatches;
    $expectedWinB = ($prediction['winB'] / 100) * $totalMatches;

    $chiSquare = 0;
    if ($expectedWinA > 0) $chiSquare += pow($actualWinA - $expectedWinA, 2) / $expectedWinA;
    if ($expectedDraw > 0) $chiSquare += pow($actualDraw - $expectedDraw, 2) / $expectedDraw;
    if ($expectedWinB > 0) $chiSquare += pow($actualWinB - $expectedWinB, 2) / $expectedWinB;

    // Degrees of freedom = 2 (3 categories - 1)
    $pValue = 1 - chi2cdf($chiSquare, 2);

    return [
        'predicted' => [
            'winA' => $prediction['winA'],
            'draw' => $prediction['draw'],
            'winB' => $prediction['winB']
        ],
        'actual' => [
            'winA' => $actualWinA,
            'draw' => $actualDraw,
            'winB' => $actualWinB
        ],
        'chi_square_test' => [
            'chi_square' => round($chiSquare, 4),
            'p_value' => round($pValue, 4),
            'significant' => $pValue < 0.05 ? 'Yes' : 'No'
        ]
    ];
}

// Implementation of natural logarithm of gamma function
function lgamma($z) {
    static $cof = [
        76.18009172947146,
        -86.50532032941677,
        24.01409824083091,
        -1.231739572450155,
        0.1208650973866179e-2,
        -0.5395239384953e-5
    ];
    
    $y = $z;
    $tmp = $z + 5.5;
    $tmp -= ($z + 0.5) * log($tmp);
    $ser = 1.000000000190015;
    
    for ($j = 0; $j <= 5; $j++) {
        $y += 1;
        $ser += $cof[$j] / $y;
    }
    
    return -$tmp + log(2.5066282746310005 * $ser / $z);
}

// Helper function for chi-square distribution
function chi2cdf($x, $df) {
    if ($x < 0) return 0;
    return igamma($df/2, $x/2);
}

// Helper function for incomplete gamma function
function igamma($a, $x) {
    if ($x <= 0) return 0;
    if ($x < $a + 1) {
        // Use series expansion
        $sum = 1 / $a;
        $term = $sum;
        for ($i = 1; $i < 100; $i++) {
            $term *= $x / ($a + $i);
            $sum += $term;
            if ($term < 1e-10 * $sum) break;
        }
        return $sum * exp(-$x + $a * log($x) - lgamma($a));
    }
    // Use continued fraction for large x
    $b = $x + 1 - $a;
    $c = 1 / 1e-30;
    $d = 1 / $b;
    $h = $d;
    for ($i = 1; $i <= 100; $i++) {
        $a_i = -$i * ($i - $a);
        $b = $b + 2;
        $d = $a_i * $d + $b;
        $c = $b + $a_i / $c;
        $d = 1 / $d;
        $del = $d * $c;
        $h = $h * $del;
        if (abs($del - 1) < 1e-10) break;
    }
    return 1 - $h * exp(-$x + $a * log($x) - lgamma($a));
}