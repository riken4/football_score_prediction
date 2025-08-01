<?php
session_start();
require 'config.php';
require 'get_seasons.php';  // Add this to get access to seasons

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get available seasons and selected season
$seasons = get_available_seasons();
$selected_season = isset($_GET['season']) ? $_GET['season'] : $seasons[0];

if (!isset($_GET['team_id']) || empty($_GET['team_id'])) {
    header('Location: dashboard.php');
    exit();
}

$team_id = $_GET['team_id'];

// Load match data for the selected season
$json = file_get_contents("data/{$selected_season}.json");
$matches = json_decode($json, true);

// Filter matches for this team
$team_matches = array_filter($matches, function ($match) use ($team_id) {
    return $match['home_team'] === $team_id || $match['away_team'] === $team_id;
});

// Sort matches by date (most recent first)
usort($team_matches, function($a, $b) {
    return strtotime($b['MatchDate']) - strtotime($a['MatchDate']);
});

// Calculate team statistics
$stats = [
    'total_matches' => 0,
    'wins' => 0,
    'draws' => 0,
    'losses' => 0,
    'goals_for' => 0,
    'goals_against' => 0,
    'clean_sheets' => 0
];

foreach ($team_matches as $match) {
    $stats['total_matches']++;
    
    $is_home = ($match['home_team'] === $team_id);
    $team_goals = $is_home ? $match['home_goals'] : $match['away_goals'];
    $opponent_goals = $is_home ? $match['away_goals'] : $match['home_goals'];
    
    $stats['goals_for'] += $team_goals;
    $stats['goals_against'] += $opponent_goals;
    
    if ($team_goals > $opponent_goals) {
        $stats['wins']++;
    } elseif ($team_goals < $opponent_goals) {
        $stats['losses']++;
    } else {
        $stats['draws']++;
    }
    
    if ($opponent_goals === 0) {
        $stats['clean_sheets']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($team_id) ?> - Team Details</title>
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

        .match-row:hover td {
            background-color: #A27B5C !important;
            color: #DCD7C9 !important;
            cursor: pointer;
        }

        .result-win { 
            color: #28a745 !important; 
            font-weight: bold;
        }
        .result-loss { 
            color: #dc3545 !important; 
            font-weight: bold;
        }
        .result-draw { 
            color: #ffc107 !important; 
            font-weight: bold;
        }

        h2, h3, h5 {
            color: #DCD7C9;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .card-title {
            margin-bottom: 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?= htmlspecialchars($team_id) ?> - Team History (<?= htmlspecialchars($selected_season) ?>)</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="card-title mb-0"><?= htmlspecialchars($team_id) ?> - Season Statistics</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Record</h5>
                                <p class="mb-1">Matches Played: <?= $stats['total_matches'] ?></p>
                                <p class="mb-1">Wins: <span class="text-success"><?= $stats['wins'] ?></span></p>
                                <p class="mb-1">Draws: <span class="text-warning"><?= $stats['draws'] ?></span></p>
                                <p class="mb-1">Losses: <span class="text-danger"><?= $stats['losses'] ?></span></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Goals</h5>
                                <p class="mb-1">Scored: <?= $stats['goals_for'] ?></p>
                                <p class="mb-1">Conceded: <?= $stats['goals_against'] ?></p>
                                <p class="mb-1">Difference: <?= $stats['goals_for'] - $stats['goals_against'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <h5>Clean Sheets</h5>
                                <p class="mb-1"><?= $stats['clean_sheets'] ?></p>
                                <p class="mb-1">Clean Sheet %: <?= round(($stats['clean_sheets'] / $stats['total_matches']) * 100, 1) ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Match History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Home Team</th>
                                        <th class="text-center">Score</th>
                                        <th>Away Team</th>
                                        <th class="text-center">Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_matches as $match): 
                                        $is_home = ($match['home_team'] === $team_id);
                                        $team_goals = $is_home ? $match['home_goals'] : $match['away_goals'];
                                        $opponent_goals = $is_home ? $match['away_goals'] : $match['home_goals'];
                                        
                                        if ($team_goals > $opponent_goals) {
                                            $result_class = 'result-win';
                                            $result_text = 'W';
                                        } elseif ($team_goals < $opponent_goals) {
                                            $result_class = 'result-loss';
                                            $result_text = 'L';
                                        } else {
                                            $result_class = 'result-draw';
                                            $result_text = 'D';
                                        }
                                    ?>
                                        <tr class="match-row" onclick="window.location='match_details.php?date=<?= urlencode($match['MatchDate']) ?>&home_team=<?= urlencode($match['home_team']) ?>&away_team=<?= urlencode($match['away_team']) ?>'">
                                            <td><?= htmlspecialchars($match['MatchDate']) ?></td>
                                            <td><?= htmlspecialchars($match['home_team']) ?></td>
                                            <td class="text-center"><?= $match['home_goals'] ?> - <?= $match['away_goals'] ?></td>
                                            <td><?= htmlspecialchars($match['away_team']) ?></td>
                                            <td class="text-center <?= $result_class ?>"><?= $result_text ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
