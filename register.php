<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

if (isset($_POST['submit'])) {
    $fullname = $_POST['fullname'];
    $UserName = $_POST['username'];
    $Number = $_POST['phone'];
    $Email = $_POST['email'];
    $Address = $_POST['address'];
    $Password = $_POST['password'];
    $hashedPassword = password_hash($Password, PASSWORD_DEFAULT);

    $profile_picture = $_FILES['profile_picture'];
    $file_name = basename($profile_picture['name']);
    $file_tmp = $profile_picture['tmp_name'];
    $file_error = $profile_picture['error'];

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $file_destination = $upload_dir . time() . '_' . $file_name;

    // Check if user or email already exists
    $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE username = ? OR email = ?");
    $stmt->execute([$UserName, $Email]);
    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Username or email already exists');</script>";
    } else {
        if ($file_error === 0 && move_uploaded_file($file_tmp, $file_destination)) {
            $insert = $pdo->prepare("INSERT INTO tbl_user 
                (UserName, fullname, Number, Email, Address, profile_picture, Password) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $success = $insert->execute([
                $UserName, $fullname, $Number, $Email, $Address, $file_destination, $hashedPassword
            ]);

            if ($success) {
                echo "<script>alert('Registration successful! Redirecting to login...'); window.location.href='login.php';</script>";
                exit();
            } else {
                echo "<script>alert('Database error during registration.');</script>";
            }
        } else {
            echo "<script>alert('File upload error');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930;
            color: #DCD7C9;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reg {
            background-color: #3F4E44;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 1s ease-in-out;
        }

        .reg h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #DCD7C9;
        }

        .form-label {
            color: #DCD7C9;
        }

        .form-control {
            background-color: #DCD7C9;
            border: none;
            border-radius: 8px;
            padding: 5px;
            margin-bottom: 10px;
        }

        .form-control:focus {
            border: 2px solid #A27B5C;
            box-shadow: 0 0 5px #A27B5C;
        }

        .btn-primary {
            background-color: #A27B5C;
            border: none;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #90694E;
        }

        input[type="file"] {
            background-color: #DCD7C9;
            padding: 5px;
            border-radius: 8px;
            margin-top: 5px;
        }

        .loginb {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="reg">
    <h1>Register</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="fullname" class="form-label">Full Name:</label>
        <input type="text" id="fullname" name="fullname" class="form-control" required>

        <label for="username" class="form-label">Username:</label>
        <input type="text" id="username" name="username" class="form-control" required>

        <label for="phone" class="form-label">Phone:</label>
        <input type="tel" id="phone" name="phone" pattern="98[0-9]{8}" title="Phone number must start with 98 and be 10 digits long" class="form-control" required>

        <label for="email" class="form-label">Email:</label>
        <input type="email" id="email" name="email" class="form-control" required>

        <label for="address" class="form-label">Address:</label>
        <input type="text" id="address" name="address" class="form-control" required>

        <label for="password" class="form-label">Password:</label>
        <input type="password" id="password" name="password" class="form-control" required>

        <!-- <label for="profile_picture" class="form-label">Profile Picture:</label>
        <input type="file" name="profile_picture" class="form-control" required> -->

        <div class="loginb">
            <input type="submit" name="submit" value="Register" class="btn btn-primary">
        </div><center>
        <label for=""class="register-link mt-3">Already Registed</label>
        <a href="login.php">login</a></center>
    </form>
</div>

</body>
</html>
