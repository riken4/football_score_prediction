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
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930; /* Dark background like in the image */
            color: #DCD7C9; /* Soft light text */
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            background-color: #3F4E44; /* Card background */
            color: #DCD7C9;
            border: none;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        .list-group-item {
            background-color: #DCD7C9; /* Light background for items */
            color: #3F4E44;
            border: none;
            margin-bottom: 8px;
            border-radius: 8px;
            font-weight: 500;
        }

        .list-group-item:hover {
            background-color: #A27B5C;
            color: white;
        }

        .text-danger {
            color: #8C4B4B !important;
        }

        .card-title {
            font-weight: bold;
            font-size: 1.6rem;
        }

        .lead {
            font-size: 1.1rem;
            font-weight: 400;
        }
    </style>
</head>
<body>

<?php include 'navbar1.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h3>
                    <p class="lead">Here’s your dashboard. Use the options below to navigate:</p>

                    <div class="list-group">
                        <!-- <a href="display.php" class="list-group-item list-group-item-action">📋 Match Detail</a> -->
                        <a href="prediction_form.php" class="list-group-item list-group-item-action">📈 Predict Result</a>
                        <a href="calculation.php" class="list-group-item list-group-item-action">⚽ Calculate Score</a>
                        <a href="dashboard.php" class="list-group-item list-group-item-action">👤 My Team</a>
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
