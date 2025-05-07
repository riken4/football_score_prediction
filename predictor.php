
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

function adjustStrength($attack, $defense, $yellow, $red) {
    $penalty = 1 - (0.02 * $yellow + 0.08 * $red);
    $penalty = max(0.6, $penalty);
    return [
        'attack' => max(0.1, $attack * $penalty),
        'defense' => max(0.1, $defense * $penalty),
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
    $teamA = adjustStrength($statsA['attack'], $statsA['defense'], $statsA['yellow_cards'], $statsA['red_cards']);
    $teamB = adjustStrength($statsB['attack'], $statsB['defense'], $statsB['yellow_cards'], $statsB['red_cards']);

    $lambdaA = $teamA['attack'] / $teamB['defense'] * 1.35;
    $lambdaB = $teamB['attack'] / $teamA['defense'];

    $maxGoals = 5;
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

function handlePrediction($teamA, $teamB, $season) {
    $matches = loadMatchData($season);
    $dataA = calculateTeamStats($matches, $teamA);
    $dataB = calculateTeamStats($matches, $teamB);

    if (!$dataA || !$dataB) {
        return ['error' => 'Invalid team names or no data found for selected season.'];
    }

    return predictScores($dataA, $dataB);
}
