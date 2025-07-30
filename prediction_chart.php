<?php
require_once 'predictor.php';

$teamA = $_GET['teamA'] ?? '';
$teamB = $_GET['teamB'] ?? '';
$season = $_GET['season'] ?? '';
$evaluation = null;
$error = null;

$teams = [
    "West Ham", "Swansea", "Arsenal", "Reading", "Spurs", "Norwich", "Wigan", "QPR",
    "Everton", "Newcastle", "Man City", "Stoke", "Man Utd", "Aston Villa", "Sunderland",
    "West Brom", "Chelsea", "Southampton", "Liverpool", "Fulham"
];
sort($teams); // Sort teams alphabetically

// Get available seasons and add "All Seasons" option
$seasons = getAvailableSeasons();
$availableSeasons = array_filter($seasons, function($season) {
    return $season !== 'data'; // Exclude data.json
});
sort($availableSeasons); // Sort seasons
array_unshift($availableSeasons, 'all'); // Add "all" option at the beginning

// Function to format season display
function formatSeasonDisplay($season) {
    if ($season === 'all') {
        return 'All Seasons (2012-2016)';
    }
    return $season;
}

if ($teamA && $teamB && $season) {
    $evaluation = evaluatePredictionAccuracy($teamA, $teamB, $season);
    if (isset($evaluation['error'])) {
        $error = $evaluation['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Match Prediction Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #2C3930;
            color: #2C3930;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #FFF5E1;
            color: #2C3930;
            border: none;
        }
        .list-group-item {
            border: none;
            font-weight: 500;
        }
        a {
            color: #A27B5C;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        a:hover {
            text-decoration: underline;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        select.form-select {
            background-color: white;
            border: 1px solid #ced4da;
        }
        select.form-select:focus {
            border-color: #A27B5C;
            box-shadow: 0 0 0 0.25rem rgba(162, 123, 92, 0.25);
        }
        .btn-primary {
            background-color: #A27B5C;
            border-color: #A27B5C;
        }
        .btn-primary:hover {
            background-color: #8B6B4F;
            border-color: #8B6B4F;
        }
        .season-info {
            font-size: 0.9em;
            color: #666;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-4 text-light">Match Prediction Analysis</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$teamA || !$teamB || !$season): ?>
            <div class="card shadow p-4 mb-5 rounded">
                <div class="form-container">
                    <h3 class="mb-4">Enter Match Details</h3>
                    <form action="prediction_chart.php" method="GET">
                        <div class="mb-3">
                            <label for="teamA" class="form-label">Team A</label>
                            <select class="form-select" id="teamA" name="teamA" required>
                                <option value="">Select Team A</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo htmlspecialchars($team); ?>" <?php echo $teamA === $team ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($team); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="teamB" class="form-label">Team B</label>
                            <select class="form-select" id="teamB" name="teamB" required>
                                <option value="">Select Team B</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo htmlspecialchars($team); ?>" <?php echo $teamB === $team ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($team); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="season" class="form-label">Season</label>
                            <select class="form-select" id="season" name="season" required>
                                <option value="">Select Season</option>
                                <?php foreach ($availableSeasons as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $season === $s ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(formatSeasonDisplay($s)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="season-info">
                                Available seasons: 2012-13, 2013-14, 2014-15, 2015-16
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Get Prediction</button>
                    </form>
                </div>
            </div>
        <?php elseif ($evaluation): ?>
            <div class="card shadow p-4 mb-5 rounded">
                <h2 class="mb-4"><?php echo htmlspecialchars($teamA); ?> vs <?php echo htmlspecialchars($teamB); ?> 
                    (<?php echo htmlspecialchars(formatSeasonDisplay($season)); ?>)</h2>

                <!-- Grid Row for Text and Chart -->
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="stats-container">
                            <h3>Statistical Analysis</h3>
                            <p>Chi-square value: <?php echo $evaluation['chi_square_test']['chi_square']; ?></p>
                            <p>P-value: <?php echo $evaluation['chi_square_test']['p_value']; ?></p>
                            <p>Statistically significant: <?php echo $evaluation['chi_square_test']['significant']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <canvas id="predictionChart" height="200" style="width: 100%;"></canvas>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="prediction_chart.php" class="btn btn-primary">Make Another Prediction</a>
                </div>
            </div>
        <?php endif; ?>

        <a href="index.php">← Back to Prediction</a>
    </div>

    <?php if ($evaluation): ?>
    <script>
        const ctx = document.getElementById('predictionChart').getContext('2d');
        const data = {
            labels: ['<?php echo htmlspecialchars($teamA); ?> Win', 'Draw', '<?php echo htmlspecialchars($teamB); ?> Win'],
            datasets: [
                {
                    label: 'Predicted Probabilities (%)',
                    data: [
                        <?php echo $evaluation['predicted']['winA']; ?>,
                        <?php echo $evaluation['predicted']['draw']; ?>,
                        <?php echo $evaluation['predicted']['winB']; ?>
                    ],
                    backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
                    borderRadius: 5,
                    barThickness: 30
                },
                {
                    label: 'Actual Results (Count)',
                    data: [
                        <?php echo $evaluation['actual']['winA']; ?>,
                        <?php echo $evaluation['actual']['draw']; ?>,
                        <?php echo $evaluation['actual']['winB']; ?>
                    ],
                    backgroundColor: ['rgba(76, 175, 80, 0.5)', 'rgba(255, 193, 7, 0.5)', 'rgba(244, 67, 54, 0.5)'],
                    borderRadius: 5,
                    barThickness: 30
                }
            ]
        };

        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#2C3930'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#2C3930'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#2C3930'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Predicted vs Actual Results',
                        color: '#2C3930',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>