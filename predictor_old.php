<?php

function getAvailableSeasons($dataDir = __DIR__ . '/data') {
    $files = glob($dataDir . '/*.json');
    return array_map(function ($file) {
        return basename($file, '.json');
    }, $files);
}

// function loadMatchData($season = "2015-16") {
//     $file = __DIR__ . "/data/$season.json";
//     if (!file_exists($file)) {
//         die("Match data for season $season not found.");
//     }
//     return json_decode(file_get_contents($file), true);
// }
function loadMatchData($season = "2014-15", $dataDir = __DIR__ . '/data') {
    if ($season === 'all') {
        $files = glob($dataDir . '/*.json');
    } elseif (is_array($season)) {
        $files = array_map(function ($s) use ($dataDir) {
            return "$dataDir/$s.json";
        }, $season);
    } else {
        $file = "$dataDir/$season.json";
        if (!file_exists($file)) {
            die("Match data for season $season not found.");
        }
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
    $matches = array_filter($data, function ($match) use ($team) {
        return $match['home_team'] === $team || $match['away_team'] === $team;
    });

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

// Handle input
$teamA = $_GET['teamA'] ?? '';
$teamB = $_GET['teamB'] ?? '';
$season = $_GET['season'] ?? '2014-15';
$availableSeasons = getAvailableSeasons();

echo "<h1>Football Score Prediction</h1>";

if ($teamA && $teamB) {
    if ($season === 'all') {
        $matches = loadMatchData('all');
    } else {
        $matches = loadMatchData($season);
    }
    
    $dataA = calculateTeamStats($matches, $teamA);
    $dataB = calculateTeamStats($matches, $teamB);

    if (!$dataA || !$dataB) {
        echo "<p style='color:red;'>Invalid team names or no data found for selected season.</p>";
    } else {
        $prediction = predictScores($dataA, $dataB);

        echo "<h2>Match Prediction: $teamA vs $teamB ($season)</h2>";
        echo "<p><strong>$teamA Win:</strong> {$prediction['winA']}%</p>";
        echo "<p><strong>Draw:</strong> {$prediction['draw']}%</p>";
        echo "<p><strong>$teamB Win:</strong> {$prediction['winB']}%</p>";

        echo "<h3>Score Probabilities:</h3><ul>";
        foreach ($prediction['details'] as $score => $prob) {
            if ($prob > 0.01) {
                echo "<li>$score: " . round($prob * 100, 2) . "%</li>";
            }
        }
        echo "</ul>";
    }

    echo "<hr>";
}
?>

<form method="get">
    <label>Team A: <input name="teamA" value="<?= htmlspecialchars($teamA) ?>" required></label><br>
    <label>Team B: <input name="teamB" value="<?= htmlspecialchars($teamB) ?>" required></label><br>
    <label>Season: 
        <select name="season" required>
            <!-- <?php foreach ($availableSeasons as $s): ?>
                <option value="<?= $s ?>" <?= $s === $season ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?> -->
            <option value="all" <?= $season === 'all' ? 'selected' : '' ?>>All Seasons</option>
<?php foreach ($availableSeasons as $s): ?>
    <option value="<?= $s ?>" <?= $s === $season ? 'selected' : '' ?>><?= $s ?></option>
<?php endforeach; ?>

        </select>
    </label><br><br>
    <button type="submit">Predict</button>
</form>
