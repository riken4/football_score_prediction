<?php
session_start();


function getActualResults($teamA, $teamB, $season) {
    $dataFile = __DIR__ . "/data/{$season}.json";
    if (!file_exists($dataFile)) return ['winA' => 0, 'draw' => 0, 'winB' => 0];
    $matches = json_decode(file_get_contents($dataFile), true);
    $winA = $draw = $winB = 0;
    foreach ($matches as $match) {
        if (
            ($match['home_team'] === $teamA && $match['away_team'] === $teamB) ||
            ($match['home_team'] === $teamB && $match['away_team'] === $teamA)
        ) {
            if ($match['home_goals'] > $match['away_goals']) {
                if ($match['home_team'] === $teamA) $winA++;
                else $winB++;
            } elseif ($match['home_goals'] < $match['away_goals']) {
                if ($match['away_team'] === $teamA) $winA++;
                else $winB++;
            } else {
                $draw++;
            }
        }
    }
    return ['winA' => $winA, 'draw' => $draw, 'winB' => $winB];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Match Predictor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .form-section {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }

        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            transition: border-color 0.3s ease;
        }

        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .teams-container {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
        }

        .team-select {
            text-align: center;
        }

        .vs-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2em;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .predict-btn {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .predict-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
        }

        .predict-btn:active {
            transform: translateY(0);
        }

        .results-section {
            padding: 40px;
            background: #f8f9fa;
        }

        .prediction-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .match-title {
            text-align: center;
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .probabilities-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .probability-card {
            text-align: center;
            padding: 25px;
            border-radius: 15px;
            color: white;
            font-weight: bold;
        }

        .home-win {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }

        .draw {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .away-win {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .probability-value {
            font-size: 2.5em;
            margin: 10px 0;
        }

        .expected-goals {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-align: center;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .expected-goals h3 {
            margin-bottom: 15px;
        }

        .goals-display {
            font-size: 2.5em;
            font-weight: bold;
        }

        .final-prediction {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .final-prediction h3 {
            margin-bottom: 15px;
        }

        .prediction-result {
            font-size: 2em;
            font-weight: bold;
            margin: 15px 0;
        }

        .confidence {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .model-info {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
        }

        .model-info h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .info-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }

        .info-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #2c3e50;
        }

        .info-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .score-probabilities {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .score-probabilities h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.5em;
        }

        .text-muted {
            color: #7f8c8d;
            font-size: 0.95em;
            margin-bottom: 20px;
        }

        .score-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
        }

        .score-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .score-item:hover {
            transform: translateY(-2px);
        }

        .score {
            font-weight: bold;
            font-size: 1.1em;
        }

        .probability {
            font-weight: bold;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .teams-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .probabilities-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚽ Football Match Predictor</h1>
        </div>

        <div class="form-section">
            <form method="POST" action="">
                <div class="teams-container">
                    <div class="team-select">
                        <div class="form-group">
                            <label for="team1">🏠 Home Team</label>
                            <select name="team1" id="team1" required>
                                <option value="">Select Home Team</option>
                                <option value="Arsenal">Arsenal</option>
                                <option value="Aston Villa">Aston Villa</option>
                                <option value="Bournemouth">Bournemouth</option>
                                <option value="Chelsea">Chelsea</option>
                                <option value="Crystal Palace">Crystal Palace</option>
                                <option value="Everton">Everton</option>
                                <option value="Leicester">Leicester</option>
                                <option value="Liverpool">Liverpool</option>
                                <option value="Man City">Man City</option>
                                <option value="Man Utd">Man Utd</option>
                                <option value="Newcastle">Newcastle</option>
                                <option value="Norwich">Norwich</option>
                                <option value="Southampton">Southampton</option>
                                <option value="Spurs">Spurs</option>
                                <option value="Stoke">Stoke</option>
                                <option value="Sunderland">Sunderland</option>
                                <option value="Swansea">Swansea</option>
                                <option value="Watford">Watford</option>
                                <option value="West Brom">West Brom</option>
                                <option value="West Ham">West Ham</option>
                            </select>
                        </div>
                    </div>

                    <div class="vs-badge">VS</div>

                    <div class="team-select">
                        <div class="form-group">
                            <label for="team2">✈️ Away Team</label>
                            <select name="team2" id="team2" required>
                                <option value="">Select Away Team</option>
                                <option value="Arsenal">Arsenal</option>
                                <option value="Aston Villa">Aston Villa</option>
                                <option value="Bournemouth">Bournemouth</option>
                                <option value="Chelsea">Chelsea</option>
                                <option value="Crystal Palace">Crystal Palace</option>
                                <option value="Everton">Everton</option>
                                <option value="Leicester">Leicester</option>
                                <option value="Liverpool">Liverpool</option>
                                <option value="Man City">Man City</option>
                                <option value="Man Utd">Man Utd</option>
                                <option value="Newcastle">Newcastle</option>
                                <option value="Norwich">Norwich</option>
                                <option value="Southampton">Southampton</option>
                                <option value="Spurs">Spurs</option>
                                <option value="Stoke">Stoke</option>
                                <option value="Sunderland">Sunderland</option>
                                <option value="Swansea">Swansea</option>
                                <option value="Watford">Watford</option>
                                <option value="West Brom">West Brom</option>
                                <option value="West Ham">West Ham</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="season">📅 Season</label>
            
                    <select name="season" id="season" required>
                        <option value="">Select Season</option>
                        <option value="2012-13">2012-13</option>
                        <option value="2013-14">2013-14</option>
                        <option value="2014-15">2014-15</option>
                        <option value="2015-16">2015-16</option>
                        <option value="2020-21">2020-21</option>
                        <!-- Add more seasons as needed -->
                    </select>
                </div>

                <button type="submit" class="predict-btn">🎯 Get Prediction</button>
            </form>
        </div>

        <?php
        if ($_POST && isset($_POST['team1']) && isset($_POST['team2'])) {
            $team1 = $_POST['team1'];
            $team2 = $_POST['team2'];
            $season = isset($_POST['season']) ? $_POST['season'] : '';
            
            if ($team1 === $team2) {
                echo '<div class="results-section">';
                echo '<div class="error">Please select different teams for home and away.</div>';
                echo '</div>';
            } else {
                echo '<div class="results-section">';
                // echo '<div class="loading">Analyzing match data...</div>';
                
                // Run prediction with better error handling
                $python = "python";
                $script = "C:\\xampp2\\htdocs\\6workingsem-with-py\\predict_match_new.py";
                $command = "\"$python\" \"$script\" \"$team1\" \"$team2\" \"$season\" 2>&1";
                
                // Debug information
                // echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; font-family: monospace; font-size: 12px;">';
                // echo '<strong>Debug Info:</strong><br>';
                // echo 'Command: ' . $command . '<br>';
                // echo 'Team 1: ' . $team1 . '<br>';
                // echo 'Team 2: ' . $team2 . '<br>';
                // echo 'Season: ' . $season . '<br>';
                // echo 'Script exists: ' . (file_exists($script) ? 'Yes' : 'No') . '<br>';
                // echo '</div>';
                
                $output = shell_exec($command);
                
                // echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; font-family: monospace; font-size: 12px;">';
                // echo '<strong>Raw Output:</strong><br>';
                // echo htmlspecialchars($output ? $output : 'NULL') . '<br>';
                // echo 'Output length: ' . strlen($output) . '<br>';
                // echo '</div>';
                
                if ($output) {
                    $json_data = json_decode($output, true);
                    if ($json_data && !isset($json_data['error'])) {
                        // Save prediction to database
                        $user = isset($_SESSION['username']) ? $_SESSION['username'] : null;
                        $prediction_details = json_encode($json_data['prediction']);
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "football_predict_py"; // <-- change to your actual DB name
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if (!$conn->connect_error) {
                            $stmt = $conn->prepare("INSERT INTO prediction_history (username, teamA, teamB, season, winA, draw, winB, prediction_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param(
                                "ssssddds",
                                $user,
                                $team1,
                                $team2,
                                $season,
                                $json_data['prediction']['home_win'],
                                $json_data['prediction']['draw'],
                                $json_data['prediction']['away_win'],
                                $prediction_details
                            );
                            $stmt->execute();
                            $stmt->close();
                            $conn->close();
                        }
                        ?>
                        <div class="prediction-card">
                            <div class="match-title"><?php echo $team1; ?> vs <?php echo $team2; ?></div>
                            
                            <div class="probabilities-grid">
                                <div class="probability-card home-win">
                                    <h3>🏠 Home Win</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['home_win']; ?>%</div>
                                </div>
                                
                                <div class="probability-card draw">
                                    <h3>🤝 Draw</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['draw']; ?>%</div>
                                </div>
                                
                                <div class="probability-card away-win">
                                    <h3>✈️ Away Win</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['away_win']; ?>%</div>
                                </div>
                            </div>
                            
                            <?php
                            $actual = getActualResults($team1, $team2, $season);
                            $predicted = [
                                $json_data['prediction']['home_win'],
                                $json_data['prediction']['draw'],
                                $json_data['prediction']['away_win']
                            ];
                            $actualArr = [$actual['winA'], $actual['draw'], $actual['winB']];
                            ?>
                            <canvas id="predictionChart" height="200" style="width: 100%; margin-bottom: 30px;"></canvas>
                            <script>
                            const ctx = document.getElementById('predictionChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: [
                                        '<?= htmlspecialchars($team1) ?> Win',
                                        'Draw',
                                        '<?= htmlspecialchars($team2) ?> Win'
                                    ],
                                    datasets: [
                                        {
                                            label: 'Predicted Probabilities (%)',
                                            data: <?= json_encode($predicted) ?>,
                                            backgroundColor: '#CE5A67',
                                            borderRadius: 5,
                                            barThickness: 30
                                        },
                                        {
                                            label: 'Actual Results (Count)',
                                            data: <?= json_encode($actualArr) ?>,
                                            backgroundColor: '#F4BF96',
                                            borderRadius: 5,
                                            barThickness: 30
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: { beginAtZero: true, ticks: { color: '#2C3930' }},
                                        x: { ticks: { color: '#2C3930' }}
                                    },
                                    plugins: {
                                        legend: { labels: { color: '#2C3930' }},
                                        title: {
                                            display: true,
                                            text: 'Predicted vs Actual Results',
                                            color: '#2C3930',
                                            font: { size: 16 }
                                        }
                                    }
                                }
                            });
                            </script>
                            
                            <div class="expected-goals">
                                <h3>⚽ Expected Goals</h3>
                                <div class="goals-display"><?php echo $json_data['prediction']['expected_home_goals']; ?> - <?php echo $json_data['prediction']['expected_away_goals']; ?></div>
                            </div>
                            
                            <?php
                            $max_prob = max($json_data['prediction']['home_win'], $json_data['prediction']['draw'], $json_data['prediction']['away_win']);
                            $predicted_result = "";
                            if ($max_prob == $json_data['prediction']['home_win']) {
                                $predicted_result = "HOME WIN for $team1";
                            } elseif ($max_prob == $json_data['prediction']['away_win']) {
                                $predicted_result = "AWAY WIN for $team2";
                            } else {
                                $predicted_result = "DRAW";
                            }
                            ?>
                            
                            <div class="final-prediction">
                                <h3>🎯 Final Prediction</h3>
                                <div class="prediction-result"><?php echo $predicted_result; ?></div>
                                <div class="confidence">Confidence: <?php echo $max_prob; ?>%</div>
                            </div>
                            
                            <?php if (isset($json_data['prediction']['score_probabilities'])): ?>
                            <div class="score-probabilities">
                                <h3>📊 Score Probabilities</h3>
                                <p class="text-muted">This chart shows the probability of each possible final score.</p>
                                <?php
                                    $score_probs = $json_data['prediction']['score_probabilities'];
                                    $filtered = array_filter($score_probs, function($p) { return $p > 0.01; });
                                    arsort($filtered);
                                    $maxProb = max($filtered);
                                ?>
                                <div class="score-list">
                                    <?php foreach ($filtered as $score => $prob): ?>
                                        <?php
                                            $lightness = 95 - 40 * ($prob / $maxProb);
                                            $bgColor = "hsl(35, 100%, {$lightness}%)";
                                        ?>
                                        <div class="score-item" style="background-color: <?= $bgColor ?>;">
                                            <span class="score"><?= htmlspecialchars($score) ?></span>
                                            <span class="probability"><?= round($prob * 100, 2) ?>%</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <canvas id="scoreProbChart" height="400" style="width: 100%; margin-top: 30px;"></canvas>
                            </div>
                            <?php endif; ?>
                            
                            <?php
                            if (isset($json_data['model_info'])) {
                                ?>
                                <div class="model-info">
                                    <h4>📊 Model Information</h4>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['total_matches']; ?></div>
                                            <div class="info-label">Total Matches</div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['training_matches']; ?></div>
                                            <div class="info-label">Training Data</div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['available_teams']; ?></div>
                                            <div class="info-label">Teams Analyzed</div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        if (isset($filtered) && count($filtered) > 0): ?>
                        <script>
                            const scoreLabels = <?= json_encode(array_keys($filtered)) ?>;
                            const scoreData = <?= json_encode(array_map(fn($p) => round($p * 100, 2), array_values($filtered))) ?>;

                            const ctxScore = document.getElementById('scoreProbChart').getContext('2d');
                            new Chart(ctxScore, {
                                type: 'bar',
                                data: {
                                    labels: scoreLabels,
                                    datasets: [{
                                        label: 'Score Probability (%)',
                                        data: scoreData,
                                        backgroundColor: '#F4BF96',
                                        borderRadius: 5
                                    }]
                                },
                                options: {
                                    indexAxis: 'y',
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            max: Math.max(...scoreData) + 2,
                                            ticks: { color: '#2C3930' },
                                            title: {
                                                display: true,
                                                text: 'Probability (%)',
                                                color: '#2C3930'
                                            }
                                        },
                                        y: {
                                            ticks: { color: '#2C3930' }
                                        }
                                    },
                                    plugins: {
                                        legend: { display: false },
                                        title: {
                                            display: true,
                                            text: 'Most Likely Final Scores',
                                            color: '#2C3930',
                                            font: { size: 16 }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return `Chance of ${context.label}: ${context.parsed.x}%`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        </script>
                        <?php endif; ?>
                        <?php
                    } else {
                        echo '<div class="error">';
                        echo '<h4>❌ Error</h4>';
                        echo '<p>' . ($json_data['error'] ?? 'An error occurred while processing the prediction.') . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="error">';
                    echo '<h4>❌ Error</h4>';
                    echo '<p>No output received from prediction script. This could mean:</p>';
                    echo '<ul>';
                    echo '<li>Python is not installed or not in PATH</li>';
                    echo '<li>The script file does not exist</li>';
                    echo '<li>There are permission issues</li>';
                    echo '<li>The script is taking too long to execute</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html> 