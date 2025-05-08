<?php
session_start();
require 'config.php'; // to get username from session and favourite team_id

$username = $_SESSION['username'] ?? null;
if (!$username) {
    die("Please login to view your favourite team.");
}

// Step 1: Get Favourite Team ID from DB
$sql = "SELECT team_id FROM user_favourites WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username]);
$fav = $stmt->fetch();

if (!$fav) {
    die("No favourite team selected.");
}

$fav_team_id = $fav['team_id'];

// Step 2: Load JSON
$json = file_get_contents('teams.json');
$teams = json_decode($json, true);

// Step 3: Find favourite team in JSON
$fav_team = null;
foreach ($teams as $team) {
    if ($team['id'] == $fav_team_id) {
        $fav_team = $team;
        break;
    }
}

if (!$fav_team) {
    die("Favourite team not found in JSON.");
}
?>

<h2>Favourite Team: <?= htmlspecialchars($fav_team['name']) ?></h2>
<p>Manager: <?= htmlspecialchars($fav_team['manager_name']) ?></p>
<p>Wins: <?= $fav_team['total_wins'] ?> | Draws: <?= $fav_team['total_draws'] ?> | Losses: <?= $fav_team['total_losses'] ?></p>
<p>Home Wins: <?= $fav_team['home_wins'] ?> | Away Losses: <?= $fav_team['away_losses'] ?></p>
<p>Goals Scored: <?= $fav_team['goals_scored'] ?> | Goals Conceded: <?= $fav_team['goals_conceded'] ?></p>

<h3>Players:</h3>
<ul>
<?php foreach ($fav_team['players'] as $player): ?>
    <li><?= htmlspecialchars($player) ?></li>
<?php endforeach; ?>
</ul>

<h3>Fixtures:</h3>
<ul>
<?php foreach ($fav_team['fixtures'] as $fixture): ?>
    <li><?= htmlspecialchars($fixture['match_date']) ?> vs <?= htmlspecialchars($fixture['opponent']) ?> (<?= $fixture['location'] ?>)</li>
<?php endforeach; ?>
</ul>
