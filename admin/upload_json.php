<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:../login.php");
    exit;
}
?>
<?php


// Optional: Check if user is admin
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//     die("Access denied");
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../data') . '/';

        $fileTmpPath = $_FILES['json_file']['tmp_name'];
        $fileName = basename($_FILES['json_file']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($fileType === 'json') {
            $fileContents = file_get_contents($fileTmpPath);
            json_decode($fileContents);

            if (json_last_error() === JSON_ERROR_NONE) {
                $destination = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $message = "✅ File uploaded successfully.";
                } else {
                    $message = "❌ Failed to move file.";
                }
            } else {
                $message = "❌ Invalid JSON format.";
            }
        } else {
            $message = "❌ Only .json files are allowed.";
        }
    } else {
        $message = "❌ File upload error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Upload JSON Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2C3930;
            color: #FFF5E1;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #FFF5E1;
            color: #2C3930;
            border: none;
        }
        a {
            color: #A27B5C;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow p-4 rounded">
                <h2 class="text-center mb-4">📤 Upload JSON Data File</h2>

                <?php if (isset($message)) echo $message; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="json_file" class="form-label">Select JSON file</label>
                        <input type="file" class="form-control" id="json_file" name="json_file" accept=".json" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>

                <a href="admin_dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>