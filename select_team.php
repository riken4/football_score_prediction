<?php
session_start();
require 'config.php';
require 'get_seasons.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get available seasons
$seasons = get_available_seasons();
$selected_season = $_GET['season'] ?? $seasons[0];

// Get already selected teams for the current season
$stmt = $pdo->prepare("SELECT team_name FROM user_favourites WHERE username = ? AND season = ?");
$stmt->execute([$_SESSION['username'], $selected_season]);
$selected_teams = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Load match data for the selected season
$json = file_get_contents("data/{$selected_season}.json");
$matches = json_decode($json, true);

// Get all unique teams
$all_teams = [];
foreach ($matches as $match) {
    if (!in_array($match['home_team'], $all_teams)) {
        $all_teams[] = $match['home_team'];
    }
    if (!in_array($match['away_team'], $all_teams)) {
        $all_teams[] = $match['away_team'];
    }
}
sort($all_teams);

// Filter out already selected teams
$available_teams = array_diff($all_teams, $selected_teams);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Favorite Teams</title>
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
            margin-bottom: 20px;
        }

        .card-header {
            background-color: rgb(133, 101, 75);
            color: #fff;
            border: none;
            border-radius: 15px 15px 0 0 !important;
        }

        .form-control, .form-select {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
            border: none;
        }

        .form-control:focus, .form-select:focus {
            border-color: #A27B5C;
            box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
        }

        .btn-primary {
            background-color: #A27B5C;
            border: none;
            color: #DCD7C9;
        }

        .btn-primary:hover {
            background-color: #8C664E;
            color: #DCD7C9;
        }

        .btn-secondary {
            background-color: #3F4E44;
            border: 1px solid #A27B5C;
            color: #DCD7C9;
        }

        .btn-secondary:hover {
            background-color: #2C3930;
            border-color: #8C664E;
            color: #DCD7C9;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            color: #DCD7C9;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            color: #DCD7C9;
        }

        .list-group-item {
            background-color: #2C3930;
            color: #DCD7C9;
            border: 1px solid #A27B5C;
            margin-bottom: 5px;
            border-radius: 8px;
        }

        .list-group-item:hover {
            background-color: #3F4E44;
        }

        h2, h3, h4, h5 {
            color: #DCD7C9;
        }

        .alert-info {
            background-color: #2C3930;
            border: 1px solid #A27B5C;
            color: #DCD7C9;
        }

        .form-label {
            color: #DCD7C9;
            font-weight: 500;
        }

        .card-title {
            color: #DCD7C9;
            font-weight: bold;
        }

        label {
            color: #DCD7C9;
        }

        .season-selector label {
            color: #DCD7C9;
            margin-bottom: 0;
        }

        .season-selector .form-select {
            min-width: 150px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="card-title mb-0">Select Favorite Team</h2>
                            <div class="season-selector">
                                <form action="" method="get" class="d-flex align-items-center">
                                    <label for="season" class="me-2">Season:</label>
                                    <select name="season" id="season" class="form-select" onchange="this.form.submit()" style="width: auto;">
                                        <?php foreach ($seasons as $season): ?>
                                            <option value="<?= htmlspecialchars($season) ?>" 
                                                    <?= $season === $selected_season ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($season) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($available_teams)): ?>
                            <div class="alert alert-info">
                                You have already added all available teams for season <?= htmlspecialchars($selected_season) ?>!
                                <div class="mt-3">
                                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="manage_favorites.php" method="post">
                                <input type="hidden" name="season" value="<?= htmlspecialchars($selected_season) ?>">
                                <div class="mb-3">
                                    <label for="team_name" class="form-label">Select Team:</label>
                                    <select name="team_name" id="team_name" class="form-select" required>
                                        <option value="">-- Select Team --</option>
                                        <?php foreach ($available_teams as $team): ?>
                                            <option value="<?= htmlspecialchars($team) ?>"><?= htmlspecialchars($team) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Add to Favourites</button>
                                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if (!empty($selected_teams)): ?>
                            <div class="mt-4">
                                <h4>Your Current Favorites for Season <?= htmlspecialchars($selected_season) ?>:</h4>
                                <ul class="list-group">
                                    <?php foreach ($selected_teams as $team): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= htmlspecialchars($team) ?>
                                            <form action="manage_favorites.php" method="post" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="team_name" value="<?= htmlspecialchars($team) ?>">
                                                <input type="hidden" name="season" value="<?= htmlspecialchars($selected_season) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>