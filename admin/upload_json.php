<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:../login.php");
    exit;
}
?>

<?php
function validateMatchData($data) {
    if (!is_array($data)) {
        return false;
    }

    $requiredFields = [
        'home_goals',
        'away_goals',
        'yellow_cards_home_team',
        'yellow_cards_away_team',
        'red_cards_home_team',
        'red_cards_away_team'
    ];

    foreach ($data as $match) {
        foreach ($requiredFields as $field) {
            if (!isset($match[$field]) || !is_numeric($match[$field]) || $match[$field] < 0) {
                return false;
            }
        }
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../data') . '/';
        $fileTmpPath = $_FILES['json_file']['tmp_name'];
        $fileName = basename($_FILES['json_file']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($fileType === 'json') {
            $fileContents = file_get_contents($fileTmpPath);
            $jsonData = json_decode($fileContents, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (validateMatchData($jsonData)) {
                    $destination = $uploadDir . $fileName;
                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $message = "<div class='alert alert-success'>✅ File uploaded successfully.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>❌ Failed to move file.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>❌ Invalid data format. Please ensure the JSON contains valid match data with required fields: home_goals, away_goals, yellow_cards_home_team, yellow_cards_away_team, red_cards_home_team, red_cards_away_team.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>❌ Invalid JSON format.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>❌ Only .json files are allowed.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>❌ File upload error.</div>";
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

        .form-control {
            background-color: #DCD7C9;
            color: #2C3930;
            border-radius: 10px;
        }

        .form-control:focus {
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

        .alert-success,
        .alert-danger,
        .alert-warning {
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .alert-success {
            background-color: #4CAF50;
            color: #fff;
        }

        .alert-danger {
            background-color: #C0392B;
            color: #fff;
        }

        .alert-warning {
            background-color: #A27B5C;
            color: white;
        }

        a {
            color: #A27B5C;
        }

        a:hover {
            color: #8C664E;
            text-decoration: underline;
        }

        h2 {
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>
<center>
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

                
            </div>
        </div>
    </div>
</div>
</center>

</body>
</html>
