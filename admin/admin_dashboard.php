<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'admin_navbar1.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow rounded">
                <div class="card-body">
                    <h3 class="card-title mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h3>
                    <p class="lead">Here’s your dashboard. Use the options below to navigate:</p>

                    <div class="list-group">
                        <a href="admin_history.php" class="list-group-item list-group-item-action">📋 History</a>
                        <a href="upload_json.php" class="list-group-item list-group-item-action">⚽ Upload Data</a>
                        <!-- <a href="settings.php" class="list-group-item list-group-item-action">⚙️ Settings</a> -->
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
