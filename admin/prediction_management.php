<?php
session_start();
require_once '../config.php';

// Get prediction statistics by season
$stmt = $pdo->prepare("
    SELECT 
        season,
        COUNT(*) as total_predictions,
        COUNT(DISTINCT username) as unique_users,
        AVG(winA) as avg_winA,
        AVG(draw) as avg_draw,
        AVG(winB) as avg_winB
    FROM prediction_history
    GROUP BY season
    ORDER BY season DESC
");
$stmt->execute();
$prediction_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user prediction statistics
$stmt = $pdo->prepare("
    SELECT 
        username,
        COUNT(*) as predictions_made,
        COUNT(DISTINCT teamA) + COUNT(DISTINCT teamB) as unique_teams,
        COUNT(DISTINCT season) as seasons_predicted
    FROM prediction_history
    GROUP BY username
    ORDER BY predictions_made DESC
    LIMIT 10
");
$stmt->execute();
$user_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get most predicted matches
$stmt = $pdo->prepare("
    SELECT 
        teamA,
        teamB,
        COUNT(*) as prediction_count,
        AVG(winA) as avg_winA,
        AVG(draw) as avg_draw,
        AVG(winB) as avg_winB
    FROM prediction_history
    GROUP BY teamA, teamB
    ORDER BY prediction_count DESC
    LIMIT 10
");
$stmt->execute();
$match_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prediction System Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .card {
            background-color: #3F4E44;
            color: #DCD7C9;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
            height: calc(100% - 1rem);
        }

        .table {
            background-color: #2C3930;
            color: #DCD7C9;
            border-radius: 10px;
            overflow: hidden;
            font-size: 0.85rem;
        }

        .table th {
            background-color: rgb(133, 101, 75);
            color: #fff;
            border: 1px solid #555;
            white-space: nowrap;
            padding: 0.5rem;
        }

        .table td {
            background-color: #dcd7c9;
            border: 1px solid #555;
            font-size: 0.85rem;
            color: #2C3930;
            padding: 0.5rem;
        }

        .table-hover tbody tr:hover td {
            background-color: #c5beb0;
        }

        h1 {
            font-weight: bold;
            color: #DCD7C9;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: rgb(133, 101, 75) !important;
            color: #DCD7C9 !important;
            border: none;
            padding: 0.75rem;
        }

        .card-header h2 {
            font-size: 1.1rem;
            margin: 0;
        }

        .card-header.bg-success {
            background-color: #5B7B63 !important;
        }

        .card-header.bg-info {
            background-color: #6B8E9E !important;
        }

        canvas {
            background-color: #DCD7C9;
            border-radius: 10px;
            padding: 10px;
            max-height: 200px !important;
        }

        .card-body {
            padding: 0.75rem;
        }

        .table-responsive {
            max-height: 300px;
            overflow-y: auto;
        }

        /* Custom scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #2C3930;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #A27B5C;
            border-radius: 4px;
        }

        .container {
            max-width: 1400px;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>
    
    <div class="container py-3">
        <h1>Prediction System Management</h1>

        <div class="row">
            <!-- Season Statistics -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="card-title mb-0">Season Statistics</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Season</th>
                                        <th>Total</th>
                                        <th>Users</th>
                                        <th>Home Win</th>
                                        <th>Draw</th>
                                        <th>Away Win</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prediction_stats as $stat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($stat['season']) ?></td>
                                            <td><?= htmlspecialchars($stat['total_predictions']) ?></td>
                                            <td><?= htmlspecialchars($stat['unique_users']) ?></td>
                                            <td><?= number_format($stat['avg_winA'], 1) ?>%</td>
                                            <td><?= number_format($stat['avg_draw'], 1) ?>%</td>
                                            <td><?= number_format($stat['avg_winB'], 1) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <canvas id="seasonChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2 class="card-title mb-0">Top Users</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Pred.</th>
                                        <th>Teams</th>
                                        <th>Seasons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_stats as $stat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($stat['username']) ?></td>
                                            <td><?= htmlspecialchars($stat['predictions_made']) ?></td>
                                            <td><?= htmlspecialchars($stat['unique_teams']) ?></td>
                                            <td><?= htmlspecialchars($stat['seasons_predicted']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Predicted Matches -->
            <div class="col-12 mt-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h2 class="card-title mb-0">Most Predicted Matches</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Times</th>
                                        <th>Home Win</th>
                                        <th>Draw</th>
                                        <th>Away Win</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($match_stats as $stat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($stat['teamA']) ?> vs <?= htmlspecialchars($stat['teamB']) ?></td>
                                            <td><?= htmlspecialchars($stat['prediction_count']) ?></td>
                                            <td><?= number_format($stat['avg_winA'], 1) ?>%</td>
                                            <td><?= number_format($stat['avg_draw'], 1) ?>%</td>
                                            <td><?= number_format($stat['avg_winB'], 1) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create season statistics chart
        const ctx = document.getElementById('seasonChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($prediction_stats, 'season')) ?>,
                datasets: [{
                    label: 'Total Predictions',
                    data: <?= json_encode(array_column($prediction_stats, 'total_predictions')) ?>,
                    backgroundColor: 'rgba(133, 101, 75, 0.7)',
                    borderColor: 'rgb(133, 101, 75)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(44, 57, 48, 0.1)'
                        },
                        ticks: {
                            color: '#2C3930'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(44, 57, 48, 0.1)'
                        },
                        ticks: {
                            color: '#2C3930'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#2C3930'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 