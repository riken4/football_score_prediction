<?php 
session_start();
require_once 'predictor.php';

$username = $_SESSION['username'] ?? null;
$teamA = $_GET['teamA'] ?? '';
$teamB = $_GET['teamB'] ?? '';
$season = $_GET['season'] ?? '2014-15';
$prediction = null;
$error = null;
$evaluation = null;

if ($teamA && $teamB) {
    $result = handlePrediction($teamA, $teamB, $season);
    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $prediction = $result;
        $evaluation = evaluatePredictionAccuracy($teamA, $teamB, $season);
    }
}

$comment_sql = "SELECT * FROM prediction_history 
                JOIN tbl_user ON prediction_history.username = tbl_user.UserName 
                ORDER BY prediction_history.h_id DESC LIMIT 10";
$comment_stmt = $pdo->query($comment_sql);
while ($comment_row = $comment_stmt->fetch()) {
    // Optional comment UI if needed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Prediction Result</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #1F1717;
            color: #1F1717;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #FCF5ED;
            color: #1F1717;
            border: none;
        }
        .list-group-item {
            border: none;
            font-weight: 500;
        }
        a {
            color: #CE5A67;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        a:hover {
            text-decoration: underline;
            color: #F4BF96;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
    <h1 class="text-center mb-4 text-light">Prediction Result</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($prediction): ?>
        <div class="card shadow p-4 mb-5 rounded">
            <h2 class="mb-4"><?= htmlspecialchars($teamA) ?> vs <?= htmlspecialchars($teamB) ?> (<?= $season ?>)</h2>

            <div class="row align-items-center">
                <div class="col-md-6">
                    <p><strong><?= htmlspecialchars($teamA) ?> Win:</strong> <?= $prediction['winA'] ?>%</p>
                    <p><strong>Draw:</strong> <?= $prediction['draw'] ?>%</p>
                    <p><strong><?= htmlspecialchars($teamB) ?> Win:</strong> <?= $prediction['winB'] ?>%</p>
                </div>
                <div class="col-md-6">
                    <canvas id="predictionChart" height="200" style="width: 100%;"></canvas>
                </div>
            </div>

            <h5 class="mt-4">Score Probabilities</h5>
            <small class="text-muted">This chart shows the probability of each possible final score.</small>
            <?php
                $filtered = array_filter($prediction['details'], fn($p) => $p > 0.01);
                arsort($filtered);
                $maxProb = max($filtered);
            ?>
            <ul class="list-group mt-3">
                <?php foreach ($filtered as $score => $prob): ?>
                    <?php
                        $lightness = 95 - 40 * ($prob / $maxProb);
                        $bgColor = "hsl(35, 100%, {$lightness}%)";
                    ?>
                    <li class="list-group-item d-flex justify-content-between" style="background-color: <?= $bgColor ?>;">
                        <span><?= htmlspecialchars($score) ?></span>
                        <span><?= round($prob * 100, 2) ?>%</span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Horizontal bar chart canvas -->
            <canvas id="scoreProbChart" height="400" style="width: 100%; margin-top: 30px;"></canvas>
        </div>
    <?php endif; ?>

    <a href="index.php">← Back to Prediction</a>
</div>

<?php if ($prediction): ?>
<script>
    // Chart 1: Predicted vs Actual Results
    const ctx = document.getElementById('predictionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                '<?= htmlspecialchars($teamA) ?> Win',
                'Draw',
                '<?= htmlspecialchars($teamB) ?> Win'
            ],
            datasets: [
                {
                    label: 'Predicted Probabilities (%)',
                    data: [
                        <?= $prediction['winA'] ?>,
                        <?= $prediction['draw'] ?>,
                        <?= $prediction['winB'] ?>
                    ],
                    backgroundColor: ['#CE5A67', '#F4BF96', '#1F1717'],
                    borderRadius: 5,
                    barThickness: 30
                },
                {
                    label: 'Actual Results (Count)',
                    data: [
                        <?= $evaluation['actual']['winA'] ?>,
                        <?= $evaluation['actual']['draw'] ?>,
                        <?= $evaluation['actual']['winB'] ?>
                    ],
                    backgroundColor: ['rgba(206, 90, 103, 0.5)', 'rgba(244, 191, 150, 0.5)', 'rgba(31, 23, 23, 0.5)'],
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

    // Chart 2: Horizontal Score Probability Chart
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
</body>
</html>
