
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4-Team League Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #2C3930;
        color: #DCD7C9;
        font-family: 'Segoe UI', sans-serif;
    }

    .section {
        background-color: #3F4E44;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    input[type="number"] {
        background-color: #DCD7C9;
        color: #2C3930;
        border: none;
        border-radius: 5px;
        padding: 5px 10px;
        margin-right: 10px;
    }

    input[type="number"]:focus {
        outline: none;
        border: 1px solid #A27B5C;
        box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
    }

    button[type="submit"] {
        background-color: #A27B5C;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
    }

    button[type="submit"]:hover {
        background-color: #8C664E;
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        background-color: #2C3930;
        color: #DCD7C9;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    th {
        background-color: #A27B5C;
        color: white;
        padding: 10px;
        border: 1px solid #8C664E;
        text-align: center;
    }

    td {
        background-color: #DCD7C9;
        color:rgb(0, 0, 0);
        padding: 10px;
        border: 1px solid #555;
        text-align: center;
    }

    tr:nth-child(odd) td {
        background-color: #DCD7C9;
    }

    h1, h2 {
        color: #DCD7C9;
    }
</style>

</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
    <h1 class="text-center mb-4">🏆 4-Team League Match</h1>
<center>
    <form method="post" >
        <div class="section shadow">
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
                echo "<label class='form-label'>{$team1} vs {$team2}</label><br>";
                echo "<input name='scores[{$i}][team1]' type='number' min='0' max='10' placeholder='{$team1} Score' required>";
                echo "<input name='scores[{$i}][team2]' type='number' min='0' max='10' placeholder='{$team2} Score' required><br><br>";
                echo "<input name='scores[{$i}][name1]' type='hidden' value='{$team1}'>";
                echo "<input name='scores[{$i}][name2]' type='hidden' value='{$team2}'>";
            }
            
            ?>
            <button type="submit">📊 Calculate</button>
        </div>
    </form>
    </center>
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

        echo "<table class='shadow'><tr><th>Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>S</th><th>M</th><th>GD</th><th>Pts</th></tr>";
        foreach ($standings as $team => $data) {
            echo "<tr><td>{$team}</td><td>{$data['P']}</td><td>{$data['W']}</td><td>{$data['D']}</td><td>{$data['L']}</td><td>{$data['S']}</td><td>{$data['M']}</td><td>{$data['GD']}</td><td>{$data['Pts']}</td></tr>";
        }
        echo "</table>";
    }
    ?>
</div>
</body>
</html>
