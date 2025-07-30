<?php
session_start();
require_once '../config.php';

// Check if database needs to be updated
$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($stmt->rowCount() === 0) {
    header('Location: update_database.php');
    exit();
}

// Get all users with their activity statistics
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.username,
        u.email,
        u.fullName,
        u.Number as phone,
        u.Address as address,
        u.Gender as gender,
        u.profile_picture,
        u.num_likes,
        COALESCE(u.status, 'active') as status,
        COALESCE(u.created_at, NOW()) as created_at,
        u.last_login,
        COUNT(DISTINCT p.id) as total_predictions,
        COUNT(DISTINCT p.season) as seasons_active,
        COUNT(DISTINCT p.teamA) + COUNT(DISTINCT p.teamB) as unique_teams,
        GROUP_CONCAT(DISTINCT CONCAT(uf.team_name, ' (', uf.season, ')') SEPARATOR ', ') as favorite_teams
    FROM users u
    LEFT JOIN prediction_history1 p ON u.username = p.user
    LEFT JOIN user_favourites uf ON u.username = uf.username
    GROUP BY u.id, u.username, u.email, u.fullName, u.Number, u.Address, 
             u.Gender, u.profile_picture, u.num_likes, u.status, u.created_at, u.last_login
    ORDER BY u.username
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        switch ($_POST['action']) {
            case 'ban':
                $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
                
            case 'unban':
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
                
            // case 'reset_password':
            //     $temp_password = bin2hex(random_bytes(8));
            //     $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
            //     $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            //     $stmt->execute([$hashed_password, $user_id]);
            //     $_SESSION['temp_password'] = [
            //         'user_id' => $user_id,
            //         'password' => $temp_password
            //     ];
                break;
        }
        
        header('Location: user_management.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
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

        .favorite-teams {
            font-size: 0.9em;
        }
        .favorite-teams ul {
            margin: 0;
            padding: 0;
        }
        .favorite-teams li {
            margin-bottom: 3px;
            color: #2C3930;
        }
        .status-badge {
            width: 80px;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-details small {
            display: block;
            color: #555;
        }

        .btn-primary {
            background-color: #A27B5C;
            border: none;
        }

        .btn-primary:hover {
            background-color: #8C664E;
        }

        h1 {
            font-weight: bold;
            color: #DCD7C9;
        }

        .card-header {
            background-color: rgb(133, 101, 75) !important;
            color: #DCD7C9 !important;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>
    
    <div class="container py-4">
        <h1 class="mb-4">User Management</h1>
        
        <?php if (isset($_SESSION['temp_password'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            Temporary password for user ID <?= htmlspecialchars($_SESSION['temp_password']['user_id']) ?>: 
            <strong><?= htmlspecialchars($_SESSION['temp_password']['password']) ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['temp_password']); endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h4 mb-0">User List</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User Info</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Activity</th>
                                <th>Favorite Teams</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($user['profile_picture']): ?>
                                            <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                                                 alt="Profile" 
                                                 class="profile-picture me-2">
                                        <?php endif; ?>
                                        <div class="user-details">
                                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                                            <small><?= htmlspecialchars($user['fullName']) ?></small>
                               
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        Email: <?= htmlspecialchars($user['email']) ?><br>
                                        Phone: <?= htmlspecialchars($user['phone']) ?><br>
                                        Address: <?= htmlspecialchars($user['address']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge status-badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($user['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        Predictions: <?= $user['total_predictions'] ?><br>
                                        <!-- Seasons: <?= $user['seasons_active'] ?><br>
                                        Teams: <?= $user['unique_teams'] ?> -->
                                    </small>
                                </td>
                                <td class="favorite-teams">
                                    <?php 
                                    if (!empty($user['favorite_teams'])) {
                                        $favorites = explode(', ', $user['favorite_teams']);
                                        echo '<ul class="list-unstyled mb-0">';
                                        foreach ($favorites as $favorite) {
                                            echo '<li><i class=""></i> ' . htmlspecialchars($favorite) . '</li>';
                                        }
                                        echo '</ul>';
                                    } else {
                                        echo '<em>None</em>';
                                    }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button type="submit" name="action" value="ban" class="btn btn-sm btn-warning" title="Ban User">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="unban" class="btn btn-sm btn-success" title="Unban User">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <!-- <button type="submit" name="action" value="reset_password" class="btn btn-sm btn-secondary" title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button> -->
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>