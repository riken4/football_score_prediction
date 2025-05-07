
<?php
// league_match_calculator.php
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4-Team League Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* body { font-family: Arial, sans-serif; background-color: #e0efe0; padding: 20px; }
        table, th, td { border: 1px solid #000; border-collapse: collapse; padding: 8px; }
        table { margin-top: 20px; width: 100%; }
        .section { background: #cfe0cf; padding: 15px; margin-top: 20px; } */
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

    <h1>4-Team League Match</h1>
    <form method="post">
        <div class="section">
            <h2>Enter Match Results</h2>
            <?php
            $teams = ['TeamA', 'TeamB', 'TeamC', 'TeamD'];
            $matches = [
                ['TeamA', 'TeamC'],
                ['TeamB', 'TeamD'],
                ['TeamA', 'TeamB'],
                ['TeamC', 'TeamD'],
                ['TeamA', 'TeamD'],
                ['TeamB', 'TeamC']
            ];

            foreach ($matches as $i => [$team1, $team2]) {
                echo "<label>{$team1} vs {$team2}</label><br>";
                echo "<input name='scores[{$i}][team1]' type='number' min='0' placeholder='{$team1} Score' required>";
                echo "<input name='scores[{$i}][team2]' type='number' min='0' placeholder='{$team2} Score' required><br><br>";
                echo "<input name='scores[{$i}][name1]' type='hidden' value='{$team1}'>";
                echo "<input name='scores[{$i}][name2]' type='hidden' value='{$team2}'>";
            }
            ?>
            <button type="submit">Calculate</button>
        </div>
    </form>

    
    <?php
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $standings = array_fill_keys($teams, [
            'P' => 0, 'W' => 0, 'D' => 0, 'L' => 0,
            'S' => 0, 'M' => 0, 'GD' => 0, 'Pts' => 0
        ]);

        foreach ($_POST['scores'] as $match) {
            $t1 = $match['name1'];
            $t2 = $match['name2'];
            $s1 = (int)$match['team1'];
            $s2 = (int)$match['team2'];

            $standings[$t1]['P']++;
            $standings[$t2]['P']++;
            $standings[$t1]['S'] += $s1;
            $standings[$t1]['M'] += $s2;
            $standings[$t2]['S'] += $s2;
            $standings[$t2]['M'] += $s1;

            if ($s1 > $s2) {
                $standings[$t1]['W']++;
                $standings[$t2]['L']++;
                $standings[$t1]['Pts'] += 3;
            } elseif ($s1 < $s2) {
                $standings[$t2]['W']++;
                $standings[$t1]['L']++;
                $standings[$t2]['Pts'] += 3;
            } else {
                $standings[$t1]['D']++;
                $standings[$t2]['D']++;
                $standings[$t1]['Pts']++;
                $standings[$t2]['Pts']++;
            }
        }

        foreach ($teams as $team) {
            $standings[$team]['GD'] = $standings[$team]['S'] - $standings[$team]['M'];
        }

        echo "<table><tr><th>Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>S</th><th>M</th><th>GD</th><th>Pts</th></tr>";
        foreach ($standings as $team => $data) {
            echo "<tr><td>{$team}</td><td>{$data['P']}</td><td>{$data['W']}</td><td>{$data['D']}</td><td>{$data['L']}</td><td>{$data['S']}</td><td>{$data['M']}</td><td>{$data['GD']}</td><td>{$data['Pts']}</td></tr>";
        }
        echo "</table>";
    }
    ?>
</body>
</html>
