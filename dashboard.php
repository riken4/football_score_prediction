<?php
session_start();
require 'config.php';
require 'get_seasons.php';  // Add this to get access to seasons

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Get available seasons and selected season
$seasons = get_available_seasons();
$selected_season = isset($_GET['season']) ? $_GET['season'] : $seasons[0];

try {
    // Get user's favorite teams using PDO
    $stmt = $pdo->prepare("SELECT team_name FROM user_favourites WHERE username = ? AND season = ?");
    $stmt->execute([$username, $selected_season]);
    $favorite_teams = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Load match data for the selected season
    $json = file_get_contents("data/{$selected_season}.json");
    $matches = json_decode($json, true);

    // Calculate stats for favorite teams
    $team_stats = [];
    foreach ($favorite_teams as $team) {
        $team_stats[$team] = [
            'matches_played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'clean_sheets' => 0,
            'form' => []
        ];
    }

    foreach ($matches as $match) {
        foreach ($favorite_teams as $team) {
            if ($match['home_team'] === $team || $match['away_team'] === $team) {
                $team_stats[$team]['matches_played']++;
                
                $is_home = ($match['home_team'] === $team);
                $team_goals = $is_home ? $match['home_goals'] : $match['away_goals'];
                $opponent_goals = $is_home ? $match['away_goals'] : $match['home_goals'];
                
                $team_stats[$team]['goals_for'] += $team_goals;
                $team_stats[$team]['goals_against'] += $opponent_goals;
                
                if ($team_goals > $opponent_goals) {
                    $team_stats[$team]['wins']++;
                    $team_stats[$team]['form'][] = 'W';
                } elseif ($team_goals < $opponent_goals) {
                    $team_stats[$team]['losses']++;
                    $team_stats[$team]['form'][] = 'L';
                } else {
                    $team_stats[$team]['draws']++;
                    $team_stats[$team]['form'][] = 'D';
                }
                
                if ($opponent_goals === 0) {
                    $team_stats[$team]['clean_sheets']++;
                }
            }
        }
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Football Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            background-color: #3F4E44;
            color: #DCD7C9;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #2C3930;
            border-bottom: 1px solid #A27B5C;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }

        .card-body {
            padding: 20px;
        }

        .form-select {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
            border: none;
        }

        .form-select:focus {
            border-color: #A27B5C;
            box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
        }

        .btn-primary {
            background-color: #A27B5C;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #8C664E;
        }

        .btn-outline-primary {
            color: #A27B5C;
            border-color: #A27B5C;
        }

        .btn-outline-primary:hover {
            background-color: #A27B5C;
            border-color: #A27B5C;
            color: #DCD7C9;
        }

        .form-indicator {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            margin: 0 3px;
            font-weight: bold;
            font-size: 0.9em;
        }

        .form-W { 
            background-color: #2C3930; 
            color: #DCD7C9;
            border: 2px solid #28a745;
        }
        .form-D { 
            background-color: #2C3930; 
            color: #DCD7C9;
            border: 2px solid #ffc107;
        }
        .form-L { 
            background-color: #2C3930; 
            color: #DCD7C9;
            border: 2px solid #dc3545;
        }

        .alert {
            background-color: #3F4E44;
            border: 1px solid #A27B5C;
            color: #DCD7C9;
        }

        .alert-link {
            color: #A27B5C;
        }

        .alert-link:hover {
            color: #8C664E;
        }

        h1, h2, h3 {
            color: #DCD7C9;
        }

        strong {
            color: #A27B5C;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <h1 class="me-3">Your Favorite Teams</h1>
                <form action="" method="get" class="d-flex align-items-center">
                    <select name="season" id="season" class="form-select" onchange="this.form.submit()" style="width: auto;">
                        <?php foreach ($seasons as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" 
                                    <?= $s === $selected_season ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div>
                <a href="select_team.php?season=<?= urlencode($selected_season) ?>" class="btn btn-primary">Add New Team</a>
           
            </div>
        </div>

        <?php if (empty($favorite_teams)): ?>
            <div class="alert alert-info">
                You haven't added any favorite teams for season <?= htmlspecialchars($selected_season) ?> yet. 
                <a href="select_team.php?season=<?= urlencode($selected_season) ?>" class="alert-link">Add some teams</a> to see their statistics!
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($team_stats as $team => $stats): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title"><?= htmlspecialchars($team) ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Matches:</strong> <?= $stats['matches_played'] ?></p>
                                        <p><strong>Record:</strong> <?= $stats['wins'] ?>W-<?= $stats['draws'] ?>D-<?= $stats['losses'] ?>L</p>
                                        <p><strong>Goals:</strong> <?= $stats['goals_for'] ?> scored, <?= $stats['goals_against'] ?> conceded</p>
                                        <p><strong>Clean Sheets:</strong> <?= $stats['clean_sheets'] ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Recent Form:</strong></p>
                                        <div>
                                            <?php 
                                            $recent_form = array_slice($stats['form'], -5);
                                            foreach ($recent_form as $result): ?>
                                                <span class="form-indicator form-<?= $result ?>"><?= $result ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="view_team.php?team_id=<?= urlencode($team) ?>&season=<?= urlencode($selected_season) ?>" class="btn btn-outline-primary">View Full History</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 