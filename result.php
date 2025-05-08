<?php 
session_start(); // Ensure this is at the very top before any output

require_once 'predictor.php';

// Get logged-in username from session
$username = $_SESSION['username'] ?? null;

$teamA = $_GET['teamA'] ?? '';
$teamB = $_GET['teamB'] ?? '';
$season = $_GET['season'] ?? '2014-15';
$prediction = null;
$error = null;

if ($teamA && $teamB) {
    $result = handlePrediction($teamA, $teamB, $season);
    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $prediction = $result;
    }
}

if ($prediction) {
    // Insert into prediction_history including username
    $stmt = $pdo->prepare("INSERT INTO prediction_history (username, teamA, teamB, season, winA, draw, winB, prediction_details)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $username,
        $teamA,
        $teamB,
        $season,
        $prediction['winA'],
        $prediction['draw'],
        $prediction['winB'],
        json_encode($prediction['details'])
    ]);
}
?>
                       <?php
$comment_sql = "SELECT * FROM prediction_history 
                JOIN tbl_user ON prediction_history.username = tbl_user.UserName 
                ORDER BY prediction_history.h_id DESC LIMIT 10";
$comment_stmt = $pdo->query($comment_sql);

while ($comment_row = $comment_stmt->fetch()) {
    ?>
    <div class="comment_all">
        <div class="comment-box">
            <div class="comment">
                <b><?php echo htmlspecialchars($comment_row['username']); ?>:</b>
                Prediction between <?php echo htmlspecialchars($comment_row['teamA']); ?> and <?php echo htmlspecialchars($comment_row['teamB']); ?>
            </div>
        </div>
    </div>
<?php } ?>


<!DOCTYPE html>
<html lang="en">

<head>
<title>Prediction Result</title>
    <meta charset="UTF-8">
    <title>Prediction Result</title>
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
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
=<?php echo $_SESSION["username"];?>
    <h1 class="text-center mb-4 text-light">Prediction Result</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($prediction): ?>
        <div class="card shadow p-4 mb-5 rounded">
            <h2 class="mb-4"><?= htmlspecialchars($teamA) ?> vs <?= htmlspecialchars($teamB) ?> (<?= $season ?>)</h2>

            <!-- Grid Row for Text and Chart -->
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
            <?php
                $filtered = array_filter($prediction['details'], fn($p) => $p > 0.01);
                arsort($filtered);
                $maxProb = max($filtered);
            ?>
            <ul class="list-group">
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
        </div>
    <?php endif; ?>

    <a href="index.php">← Back to Prediction</a>
</div>

<?php if ($prediction): ?>
<script>
    const ctx = document.getElementById('predictionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                '<?= htmlspecialchars($teamA) ?> Win',
                'Draw',
                '<?= htmlspecialchars($teamB) ?> Win'
            ],
            datasets: [{
                label: 'Prediction %',
                data: [
                    <?= $prediction['winA'] ?>,
                    <?= $prediction['draw'] ?>,
                    <?= $prediction['winB'] ?>
                ],
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
                borderRadius: 5,
                barThickness: 30
            }]
        },
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
                    display: false
                },
                title: {
                    display: true,
                    text: 'Match Prediction Chart',
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
