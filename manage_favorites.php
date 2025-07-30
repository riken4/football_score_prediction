<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_SESSION['username'];
        $team_name = trim($_POST['team_name'] ?? '');
        $season = trim($_POST['season'] ?? '2012-13');
        $action = trim($_POST['action'] ?? 'add');

        if (!$team_name) {
            throw new Exception("Team Name is required.");
        }

        if ($action === 'remove') {
            // Remove team from favorites
            $stmt = $pdo->prepare("DELETE FROM user_favourites WHERE username = ? AND team_name = ? AND season = ?");
            $stmt->execute([$username, $team_name, $season]);
            $_SESSION['message'] = "Team removed from favorites.";
        } else {
            // First check if the combination already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_favourites WHERE username = ? AND team_name = ? AND season = ?");
            $check_stmt->execute([$username, $team_name, $season]);
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['message'] = "This team is already in your favorites list for this season.";
            } else {
                // Add new favorite team
                $stmt = $pdo->prepare("INSERT INTO user_favourites (username, team_name, season, added_date) VALUES (?, ?, ?, NOW())");
                try {
                    $stmt->execute([$username, $team_name, $season]);
                    $_SESSION['message'] = "Favorite team added successfully!";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {  // Duplicate entry error code
                        $_SESSION['message'] = "This team is already in your favorites list for this season.";
                    } else {
                        throw $e;  // Re-throw other SQL exceptions
                    }
                }
            }
        }
        
        // Redirect back to the previous page with season parameter
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
        if (strpos($redirect, 'season=') === false && strpos($redirect, 'select_team.php') !== false) {
            $redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'season=' . urlencode($season);
        }
        header("Location: $redirect");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?>
