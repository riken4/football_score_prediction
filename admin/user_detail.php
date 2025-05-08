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
</head>
<body class="bg-light">
<?php include 'admin_navbar.php'; ?>
<div class="container py-5">
    <h1 class="mb-4">📊 Prediction History</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>user</th>
                <th>Teams</th>
                <th>Season</th>
                <th>Win A</th>
                <th>Draw</th>
                <th>Win B</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($predictions as $row) ?>
                <tr>
                <?php foreach ($predictions as $row): ?>
                    <td><?= $row['username'] ?></td>
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
</body>
</html>
