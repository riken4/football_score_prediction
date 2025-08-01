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
<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Match Predictor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            background-color: #3F4E44;
            color: #DCD7C9;
            border: none;
        }

        .form-control,
        .form-select {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #A27B5C;
            box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
        }

        .btn-primary {
            background-color: #A27B5C;
            border: none;
        }

        .btn-primary:hover {
            background-color: #8C664E;
        }

        .list-group-item {
            background-color: #DCD7C9;
            color: #2C3930;
            border: none;
        }

                 .probability-card {
             background-color: #3F4E44;
             border-radius: 12px;
             padding: 15px;
             text-align: center;
             color: #DCD7C9;
             border: 2px solid #A27B5C;
         }

         .home-win {
             border-color: #27ae60;
             background: linear-gradient(135deg, #27ae60, #2ecc71);
             color: white;
         }

         .draw {
             border-color: #f39c12;
             background: linear-gradient(135deg, #f39c12, #e67e22);
             color: white;
         }

         .away-win {
             border-color: #e74c3c;
             background: linear-gradient(135deg, #e74c3c, #c0392b);
             color: white;
         }

         .probability-value {
             font-size: 1.8em;
             font-weight: bold;
             color: white;
         }

         .expected-goals {
             background: linear-gradient(135deg, #3498db, #2980b9);
             border: 2px solid #3498db;
             border-radius: 12px;
             padding: 15px;
             text-align: center;
             margin-bottom: 20px;
             color: white;
         }

         .goals-display {
             font-size: 1.8em;
             font-weight: bold;
             color: white;
         }

         .final-prediction {
             background: linear-gradient(135deg, #9b59b6, #8e44ad);
             border: 2px solid #9b59b6;
             border-radius: 12px;
             padding: 20px;
             text-align: center;
             margin-bottom: 20px;
             color: white;
         }

         .prediction-result {
             font-size: 1.5em;
             font-weight: bold;
             color: white;
             margin: 10px 0;
         }

         .confidence {
             font-size: 1em;
             color: white;
         }

        .model-info {
            background-color: #3F4E44;
            border: 1px solid #A27B5C;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .info-item {
            text-align: center;
            padding: 15px;
            background-color: #DCD7C9;
            border-radius: 10px;
            color: #2C3930;
        }

        .info-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #A27B5C;
        }

        .info-label {
            color: #2C3930;
            font-size: 0.9em;
        }

        .score-probabilities {
            background-color: #3F4E44;
            border: 1px solid #A27B5C;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

                 .score-grid {
             display: grid;
             grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
             gap: 8px;
         }

         .score-item {
             display: flex;
             justify-content: space-between;
             align-items: center;
             padding: 8px 12px;
             border-radius: 8px;
             font-weight: 600;
             background: linear-gradient(135deg, #f39c12, #e67e22);
             color: #2C3930;
             margin-bottom: 6px;
             box-shadow: 0 2px 8px rgba(0,0,0,0.1);
             font-size: 0.9em;
         }

        .score {
            font-weight: bold;
            font-size: 1.1em;
        }

        .probability {
            font-weight: bold;
            color: #2C3930;
        }

        .error {
            background-color: #721c24;
            color: #f8d7da;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .vs-badge {
            background-color: #A27B5C;
            color: #DCD7C9;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2em;
            text-align: center;
        }

        .teams-container {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .teams-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="container py-5">
        <h1 class="text-center mb-4">⚽ Football Match Predictor</h1>

        <div class="card shadow p-4 mb-5 rounded">
            <form method="POST" action="">
                <div class="teams-container">
                    <div class="mb-3">
                        <label for="team1" class="form-label">🏠 Home Team</label>
                        <select name="team1" id="team1" class="form-select" required>
                            <option value="">Select Home Team</option>
                            <option value="Arsenal">Arsenal</option>
                            <option value="Aston Villa">Aston Villa</option>
                            <option value="Bournemouth">Bournemouth</option>
                            <option value="Brentford">Brentford</option>
                            <option value="Brighton">Brighton</option>
                            <option value="Burnley">Burnley</option>
                            <option value="Chelsea">Chelsea</option>
                            <option value="Crystal Palace">Crystal Palace</option>
                            <option value="Everton">Everton</option>
                            <option value="Fulham">Fulham</option>
                            <option value="Leeds">Leeds</option>
                            <option value="Leicester">Leicester</option>
                            <option value="Liverpool">Liverpool</option>
                            <option value="Luton">Luton</option>
                            <option value="Man City">Man City</option>
                            <option value="Man Utd">Man United</option>
                            <option value="Newcastle">Newcastle</option>
                            <option value="Norwich">Norwich</option>
                            <option value="Nottingham Forest">Nottingham Forest</option>
                            <option value="Sheffield Utd">Sheffield Utd</option>
                            <option value="Southampton">Southampton</option>
                            <option value="Spurs">Tottenham</option>
                            <option value="Stoke">Stoke</option>
                            <option value="Sunderland">Sunderland</option>
                            <option value="Swansea">Swansea</option>
                            <option value="Watford">Watford</option>
                            <option value="West Brom">West Brom</option>
                            <option value="West Ham">West Ham</option>
                            <option value="Wolves">Wolves</option>
                        </select>
                    </div>

                    <div class="vs-badge">VS</div>

                    <div class="mb-3">
                        <label for="team2" class="form-label">✈️ Away Team</label>
                        <select name="team2" id="team2" class="form-select" required>
                            <option value="">Select Away Team</option>
                            <option value="Arsenal">Arsenal</option>
                            <option value="Aston Villa">Aston Villa</option>
                            <option value="Bournemouth">Bournemouth</option>
                            <option value="Brentford">Brentford</option>
                            <option value="Brighton">Brighton</option>
                            <option value="Burnley">Burnley</option>
                            <option value="Chelsea">Chelsea</option>
                            <option value="Crystal Palace">Crystal Palace</option>
                            <option value="Everton">Everton</option>
                            <option value="Fulham">Fulham</option>
                            <option value="Leeds">Leeds</option>
                            <option value="Leicester">Leicester</option>
                            <option value="Liverpool">Liverpool</option>
                            <option value="Luton">Luton</option>
                            <option value="Man City">Man City</option>
                            <option value="Man Utd">Man United</option>
                            <option value="Newcastle">Newcastle</option>
                            <option value="Norwich">Norwich</option>
                            <option value="Nottingham Forest">Nottingham Forest</option>
                            <option value="Sheffield Utd">Sheffield Utd</option>
                            <option value="Southampton">Southampton</option>
                            <option value="Spurs">Tottenham</option>
                            <option value="Stoke">Stoke</option>
                            <option value="Sunderland">Sunderland</option>
                            <option value="Swansea">Swansea</option>
                            <option value="Watford">Watford</option>
                            <option value="West Brom">West Brom</option>
                            <option value="West Ham">West Ham</option>
                            <option value="Wolves">Wolves</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="season" class="form-label">📅 Season</label>
                    <select name="season" id="season" class="form-select" required>
                        <option value="">Select Season</option>
                        <option value="2012-13">2012-13</option>
                        <option value="2013-14">2013-14</option>
                        <option value="2014-15">2014-15</option>
                        <option value="2015-16">2015-16</option>
                        <option value="2020-21">2020-21</option>
                        <option value="2021-22">2021-22</option>
                        <option value="2022-23">2022-23</option>
                        <option value="2023-24">2023-24</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">🎯 Get Prediction</button>
            </form>
        </div>

        <?php
        if ($_POST && isset($_POST['team1']) && isset($_POST['team2'])) {
            $team1 = $_POST['team1'];
            $team2 = $_POST['team2'];
            $season = isset($_POST['season']) ? $_POST['season'] : '';
            
            if ($team1 === $team2) {
                echo '<div class="card shadow p-4 mb-5 rounded">';
                echo '<div class="error">Please select different teams for home and away.</div>';
                echo '</div>';
            } else {
                echo '<div class="card shadow p-4 mb-5 rounded">';
                
                // Run prediction with better error handling
                $python = "python";
                $script = "C:\\xampp2\\htdocs\\6workingsem-with-py\\predict_match_new.py";
                $command = "\"$python\" \"$script\" \"$team1\" \"$team2\" \"$season\" 2>&1";
                
                $output = shell_exec($command);
                
                if ($output) {
                    $json_data = json_decode($output, true);
                    if ($json_data && !isset($json_data['error'])) {
                        // Save prediction to database
                        $user = isset($_SESSION['username']) ? $_SESSION['username'] : null;
                        $prediction_details = json_encode($json_data['prediction']);
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "football_predict_py";
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
                        <h2 class="text-center mb-4"><?php echo $team1; ?> vs <?php echo $team2; ?></h2>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="probability-card home-win">
                                    <h3>🏠 Home Win</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['home_win']; ?>%</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="probability-card draw">
                                    <h3>🤝 Draw</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['draw']; ?>%</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="probability-card away-win">
                                    <h3>✈️ Away Win</h3>
                                    <div class="probability-value"><?php echo $json_data['prediction']['away_win']; ?>%</div>
                                </div>
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
                        <div class="mb-4">
                            <h4 class="text-center mb-3">Predicted vs Actual Results</h4>
                            <canvas id="predictionChart" height="60"></canvas>
                        </div>
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
                                    y: { beginAtZero: true, ticks: { color: '#DCD7C9' }},
                                    x: { ticks: { color: '#DCD7C9' }}
                                },
                                plugins: {
                                    legend: { labels: { color: '#DCD7C9' }},
                                    title: {
                                        display: false
                                    }
                                }
                            }
                        });
                        </script>
                        
                        <div class="expected-goals mb-4">
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
                        
                        <div class="final-prediction mb-4">
                            <h3>🎯 Final Prediction</h3>
                            <div class="prediction-result"><?php echo $predicted_result; ?></div>
                            <div class="confidence">Confidence: <?php echo $max_prob; ?>%</div>
                        </div>
                        
                        <?php if (isset($json_data['prediction']['score_probabilities'])): ?>
                        <div class="score-probabilities mb-4">
                            <h3>📊 Score Probabilities</h3>
                            <p class="mb-3" style="color: white;">This chart shows the probability of each possible final score.</p>
                            <?php
                                $score_probs = $json_data['prediction']['score_probabilities'];
                                $filtered = array_filter($score_probs, function($p) { return $p > 0.01; });
                                arsort($filtered);
                                $maxProb = max($filtered);
                            ?>
                            <div class="score-grid mb-4">
                                <?php foreach ($filtered as $score => $prob): ?>
                                    <div class="score-item">
                                        <span class="score"><?= htmlspecialchars($score) ?></span>
                                        <span class="probability"><?= round($prob * 100, 2) ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                                                         <h4 class="text-center mb-3">Most Likely Final Scores</h4>
                             <canvas id="scoreProbChart" height="180"></canvas>
                        </div>
                        <?php endif; ?>
                        
                        <?php
                        if (isset($json_data['model_info'])) {
                            ?>
                            <div class="model-info">
                                <h4>📊 Model Information</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['total_matches']; ?></div>
                                            <div class="info-label">Total Matches</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['training_matches']; ?></div>
                                            <div class="info-label">Training Data</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <div class="info-value"><?php echo $json_data['model_info']['available_teams']; ?></div>
                                            <div class="info-label">Teams Analyzed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
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
                                            ticks: { color: '#DCD7C9' },
                                            title: {
                                                display: true,
                                                text: 'Probability (%)',
                                                color: '#DCD7C9'
                                            }
                                        },
                                        y: {
                                            ticks: { color: '#DCD7C9' }
                                        }
                                    },
                                    plugins: {
                                        legend: { display: false },
                                        title: {
                                            display: false
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>