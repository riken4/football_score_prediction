<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit;
}
?>
<?php 
require_once 'predictor.php';

$teamA = $_GET['teamA'] ?? '';
$teamB = $_GET['teamB'] ?? '';
$season = $_GET['season'] ?? '';
$availableSeasons = getAvailableSeasons();

if ($teamA && $teamB) {
   
    header("Location: result.php?teamA=" . urlencode($teamA) . "&teamB=" . urlencode($teamB) . "&season=" . urlencode($season));
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Football Score Predictor</title>
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
        }

        .form-control,
        .form-select {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
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

        .list-group-item {
            background-color: #DCD7C9;
            color: #2C3930;
            border: none;
        }

        a {
            color: #A27B5C;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<!--  Prediction Form -->
<div class="container py-5">
    <h1 class="text-center mb-4">⚽ Football Score Predictor</h1>

    <form method="get" class="card shadow p-4 mb-5 rounded" onsubmit="return validateTeams();">
    <?php
    $teams = [
        "West Ham", "Swansea", "Arsenal", "Reading", "Spurs", "Norwich", "Wigan", "QPR",
        "Everton", "Newcastle", "Man City", "Stoke", "Man Utd", "Aston Villa", "Sunderland",
        "West Brom", "Chelsea", "Southampton", "Liverpool", "Fulham"
    ];
    ?>

    <div class="mb-3">
        <label for="teamA" class="form-label">Team A</label>
        <select class="form-select" name="teamA" id="teamA" required onchange="checkTeams()">
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team ?>" <?= $team === $teamA ? 'selected' : '' ?>><?= $team ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="teamB" class="form-label">Team B</label>
        <select class="form-select" name="teamB" id="teamB" required onchange="checkTeams()">
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team ?>" <?= $team === $teamB ? 'selected' : '' ?>><?= $team ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="season" class="form-label">Season</label>
        <select name="season" id="season" class="form-select" required>
            <option value="all" <?= $season === 'all' ? 'selected' : '' ?>>All Seasons</option>
            <?php foreach ($availableSeasons as $s): ?>
                <option value="<?= $s ?>" <?= $s === $season ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="teamError" class="text-danger mb-3" style="display: none;">Team A and Team B cannot be the same.</div>

    <button type="submit" class="btn btn-primary w-100" id="submitBtn">🔮 Predict</button>
</form>

<script>
function checkTeams() {
    const teamA = document.getElementById("teamA").value;
    const teamB = document.getElementById("teamB").value;
    const error = document.getElementById("teamError");
    const submitBtn = document.getElementById("submitBtn");

    if (teamA === teamB) {
        error.style.display = "block";
        submitBtn.disabled = true;
    } else {
        error.style.display = "none";
        submitBtn.disabled = false;
    }
}

function validateTeams() {
    const teamA = document.getElementById("teamA").value;
    const teamB = document.getElementById("teamB").value;
    if (teamA === teamB) {
        alert("Team A and Team B must be different.");
        return false;
    }
    return true;
}
</script>


    <!-- <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($prediction): ?>
        <div class="card shadow p-4 mb-5 rounded">
            <h2 class="mb-3"><?= htmlspecialchars($teamA) ?> vs <?= htmlspecialchars($teamB) ?> (<?= $season ?>)</h2>
            <p><strong><?= htmlspecialchars($teamA) ?> Win:</strong> <?= $prediction['winA'] ?>%</p>
            <p><strong>Draw:</strong> <?= $prediction['draw'] ?>%</p>
            <p><strong><?= htmlspecialchars($teamB) ?> Win:</strong> <?= $prediction['winB'] ?>%</p>

            <h5 class="mt-4">🔢 Score Probabilities (≥ 1%):</h5>
            <ul class="list-group">
                <?php foreach ($prediction['details'] as $score => $prob): ?>
                    <?php if ($prob > 0.01): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= $score ?></span>
                            <span><?= round($prob * 100, 2) ?>%</span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
 -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>