<?php
session_start();
require 'config.php';
require 'get_seasons.php';  // Add this to get access to seasons

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['date']) || !isset($_GET['home_team']) || !isset($_GET['away_team'])) {
    header('Location: dashboard.php');
    exit();
}

$date = $_GET['date'];
$home_team = $_GET['home_team'];
$away_team = $_GET['away_team'];

// Get all available seasons
$seasons = get_available_seasons();
$selected_season = isset($_GET['season']) ? $_GET['season'] : $seasons[0];

// Initialize match details
$match_details = null;
$match_season = null;

// First try to find the match in the selected season
$json = file_get_contents("data/{$selected_season}.json");
$matches = json_decode($json, true);

foreach ($matches as $match) {
    if ($match['date'] === $date && 
        $match['home_team'] === $home_team && 
        $match['away_team'] === $away_team) {
        $match_details = $match;
        $match_season = $selected_season;
        break;
    }
}

// If match not found in selected season, search through all seasons
if (!$match_details) {
    foreach ($seasons as $season) {
        if ($season === $selected_season) continue; // Skip already checked season
        
        $json = file_get_contents("data/{$season}.json");
        $matches = json_decode($json, true);
        
        foreach ($matches as $match) {
            if ($match['date'] === $date && 
                $match['home_team'] === $home_team && 
                $match['away_team'] === $away_team) {
                $match_details = $match;
                $match_season = $season;
                break 2; // Break both loops
            }
        }
    }
}

if (!$match_details) {
    die("Match not found in any season");
}

// Update selected_season to the actual season where the match was found
$selected_season = $match_season;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Match Details</title>
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
        }

        .form-control:focus, .form-select:focus {
            border-color: #A27B5C;
            box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
        }

        .btn-primary, .btn-secondary {
            background-color: #A27B5C;
            border: none;
            color: #DCD7C9;
        }

        .btn-primary:hover, .btn-secondary:hover {
            background-color: #8C664E;
            color: #DCD7C9;
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
            color: #2C3930;
        }

        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: #dcd7c9;
        }

        h2, h3, h4, h5 {
            color: #DCD7C9;
        }

        .text-muted {
            color: #DCD7C9 !important;
            opacity: 0.8;
        }

        .stat-row {
            padding: 12px 0;
            border-bottom: 1px solid #A27B5C;
            color: #DCD7C9;
        }

        .team-name {
            font-weight: bold;
            font-size: 1.4em;
            color: #DCD7C9;
        }

        .score {
            font-size: 2.5em;
            font-weight: bold;
            color: #A27B5C;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        strong {
            color: #A27B5C;
        }

        .table td.text-end {
            text-align: right;
        }

        .table td.text-center {
            text-align: center;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Match Details</h2>
                    <div class="text-muted">
                        Season: <?= htmlspecialchars($selected_season) ?> | 
                        Date: <?= htmlspecialchars($match_details['date']) ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Match Score -->
                <div class="row text-center mb-4">
                    <div class="col-5">
                        <div class="team-name"><?= htmlspecialchars($match_details['home_team']) ?></div>
                    </div>
                    <div class="col-2">
                        <div class="score">
                            <?= $match_details['home_goals'] ?> - <?= $match_details['away_goals'] ?>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="team-name"><?= htmlspecialchars($match_details['away_team']) ?></div>
                    </div>
                </div>

                <!-- Match Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Match Information</h4>
                        <div class="stat-row">
                            <strong>Venue:</strong> <?= htmlspecialchars($match_details['venue']) ?>
                        </div>
                        <div class="stat-row">
                            <strong>Referee:</strong> <?= htmlspecialchars($match_details['referee']) ?>
                        </div>
                        <div class="stat-row">
                            <strong>Attendance:</strong> <?= number_format($match_details['attendance']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Goals</h4>
                        <div class="stat-row">
                            <strong>Home Goals:</strong> <?= htmlspecialchars($match_details['home_goals_details']) ?>
                        </div>
                        <div class="stat-row">
                            <strong>Away Goals:</strong> <?= htmlspecialchars($match_details['away_goals_details']) ?>
                        </div>
                    </div>
                </div>

                <!-- Match Statistics -->
                <h4>Match Statistics</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars($match_details['home_team']) ?></th>
                                <th class="text-center">Statistic</th>
                                <th class="text-end"><?= htmlspecialchars($match_details['away_team']) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $match_details['shots_on_target_home_team'] ?></td>
                                <td class="text-center">Shots on Target</td>
                                <td class="text-end"><?= $match_details['shots_on_target_away_team'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $match_details['shots_off_target_home_team'] ?></td>
                                <td class="text-center">Shots off Target</td>
                                <td class="text-end"><?= $match_details['shots_off_target_away_team'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $match_details['corners_home_team'] ?></td>
                                <td class="text-center">Corners</td>
                                <td class="text-end"><?= $match_details['corners_away_team'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $match_details['fouls_home_team'] ?></td>
                                <td class="text-center">Fouls</td>
                                <td class="text-end"><?= $match_details['fouls_away_team'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $match_details['yellow_cards_home_team'] ?></td>
                                <td class="text-center">Yellow Cards</td>
                                <td class="text-end"><?= $match_details['yellow_cards_away_team'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $match_details['red_cards_home_team'] ?></td>
                                <td class="text-center">Red Cards</td>
                                <td class="text-end"><?= $match_details['red_cards_away_team'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                 
                    <a href="dashboard.php?season=<?= urlencode($selected_season) ?>" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 