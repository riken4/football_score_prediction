<?php 
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $UserName = trim($_POST['username']);
    $Password = trim($_POST['password']);
    $inputUsertype = $_POST['usertype'];

    if ($inputUsertype == 1) {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username");
    } else if ($inputUsertype == 2) {
        $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE username = :username");
    }

    $stmt->execute(['username' => $UserName]);

    $user = $stmt->fetch();

    if ($user && password_verify($Password, $user['password'])) {
        $_SESSION['username'] = $UserName;

        if ($inputUsertype == 1) {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php"); // redirecting user to a separate dashboard
        }
        exit();
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href = 'login.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-container {
            max-width: 500px;
            margin: auto;
            margin-top: 100px;
            background-color: #3F4E44;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(220, 215, 201, 0.2);
        }

        .form-label,
        .form-control {
            color: #DCD7C9;
        }

        .form-select {
            color: #3F4E44;
        }

        .form-control, .form-select {
            background-color: #DCD7C9;
            border: none;
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(162, 123, 92, 0.5);
        }

        .btn-login {
            background-color: #A27B5C;
            border: none;
            width: 100%;
        }

        .btn-login:hover {
            background-color: #8C664E;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
        }

        .register-link a {
            color: #A27B5C;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">🔐 Login</h2>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-4">
                <label for="usertype" class="form-label">User Type</label>
                <select name="usertype" id="usertype" class="form-select">
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                </select>
            </div>

            <button type="submit" name="login" class="btn btn-login">Login</button>

            <p class="register-link mt-3">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
