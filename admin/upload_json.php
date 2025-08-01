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

// Handle file deletion
if (isset($_POST['delete_file']) && isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    $uploadDir = realpath(__DIR__ . '/../data') . '/';
    $filePath = $uploadDir . $filename;
    
    // Security check: ensure file is in the data directory
    if (file_exists($filePath) && strpos(realpath($filePath), realpath($uploadDir)) === 0) {
        if (unlink($filePath)) {
            $message = "<div class='alert alert-success'>✅ File '$filename' deleted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to delete file '$filename'.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Invalid file path or file not found.</div>";
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
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

// Get list of existing JSON files
$uploadDir = realpath(__DIR__ . '/../data') . '/';
$existingFiles = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $filePath = $uploadDir . $file;
            $fileSize = filesize($filePath);
            $fileDate = date("F j, Y g:i A", filemtime($filePath));
            $existingFiles[] = [
                'name' => $file,
                'size' => $fileSize,
                'date' => $fileDate
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Manage JSON Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            margin-bottom: 20px;
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

        .btn-danger {
            background-color: #C0392B;
            border: none;
        }

        .btn-danger:hover {
            background-color: #A93226;
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

        .file-item {
            background-color: #2C3930;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #A27B5C;
        }

        .file-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .file-details {
            flex: 1;
        }

        .file-actions {
            flex-shrink: 0;
        }

        .file-size {
            color: #A27B5C;
            font-size: 0.9em;
        }

        .file-date {
            color: #8C664E;
            font-size: 0.85em;
        }

        .section-divider {
            border-top: 2px solid #A27B5C;
            margin: 30px 0;
            opacity: 0.3;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>
<center>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Upload Section -->
            <div class="card shadow p-4 rounded">
                <h2 class="text-center mb-4">
                    <i class="fas fa-upload"></i> Upload New JSON Data File
                </h2>

                <?php if (isset($message)) echo $message; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="json_file" class="form-label">
                            <i class="fas fa-file-json"></i> Select JSON file
                        </label>
                        <input type="file" class="form-control" id="json_file" name="json_file" accept=".json" required>
                        <!-- <div class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            File must contain valid match data with required fields: home_goals, away_goals, yellow_cards_home_team, yellow_cards_away_team, red_cards_home_team, red_cards_away_team
                        </div> -->
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                </form>
            </div>

            <div class="section-divider"></div>

            <!-- Delete Section -->
            <div class="card shadow p-4 rounded">
                <h2 class="text-center mb-4">
                    <i class="fas fa-trash"></i> Manage Existing Files
                </h2>

                <?php if (empty($existingFiles)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No JSON files found in the data directory.</p>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <!-- <p class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Click the delete button to remove a file permanently. This action cannot be undone.
                        </p> -->
                    </div>
                    
                    <?php foreach ($existingFiles as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-details">
                                    <h5 class="mb-1">
                                        <i class="fas fa-file-json"></i> <?php echo htmlspecialchars($file['name']); ?>
                                    </h5>
                                  
                                </div>
                                <div class="file-actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($file['name']); ?>? This action cannot be undone.');">
                                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" name="delete_file" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</center>

</body>
</html>
