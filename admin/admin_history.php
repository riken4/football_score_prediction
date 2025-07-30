<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
?>
<?php
require_once '../config.php';
$stmt = $pdo->query("SELECT * FROM prediction_history ORDER BY created_at DESC");
$predictions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Prediction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
        }

        .card, .alert {
            background-color: #3F4E44;
            color: #DCD7C9;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .form-control, .form-select {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
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

        .table {
            background-color: #2C3930;
            color: #DCD7C9;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background-color: rgb(133, 101, 75);
            color: #fff;
            border: 1px solid #555;
        }

        .table td {
            background-color: #dcd7c9;
            border: 1px solid #555;
            font-size: 0.85rem;
            white-space: nowrap;
            color: #2C3930;
        }

        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: #dcd7c9;
        }

        h1 {
            font-weight: bold;
            color: #DCD7C9;
        }

     
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container py-5">
    <h1 class="mb-4 text-center">📊 Prediction History</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Teams</th>
                    <th>Season</th>
                    <th>Win A</th>
                    <th>Draw</th>
                    <th>Win B</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($predictions as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['teamA']) ?> vs <?= htmlspecialchars($row['teamB']) ?></td>
                        <td><?= htmlspecialchars($row['season']) ?></td>
                        <td><?= $row['winA'] ?>%</td>
                        <td><?= $row['draw'] ?>%</td>
                        <td><?= $row['winB'] ?>%</td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
