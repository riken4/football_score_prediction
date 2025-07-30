<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:../login.php");
    exit;
}
?>

<?php
// Function to get all JSON files in the data directory
function getJsonFiles() {
    $dataDir = realpath(__DIR__ . '/../data') . '/';
    $files = glob($dataDir . '*.json');
    $jsonFiles = [];
    
    foreach ($files as $file) {
        $fileName = basename($file);
        $fileSize = filesize($file);
        $fileDate = date('Y-m-d H:i:s', filemtime($file));
        
        $jsonFiles[] = [
            'name' => $fileName,
            'size' => $fileSize,
            'date' => $fileDate,
            'path' => $file
        ];
    }
    
    return $jsonFiles;
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileName = $_POST['delete_file'];
    $dataDir = realpath(__DIR__ . '/../data') . '/';
    $filePath = $dataDir . $fileName;
    
    // Security check: ensure the file is within the data directory
    if (strpos(realpath($filePath), realpath($dataDir)) === 0 && file_exists($filePath)) {
        if (unlink($filePath)) {
            $message = "<div class='alert alert-success'>✅ File '$fileName' deleted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to delete file '$fileName'.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Invalid file or security violation.</div>";
    }
}

$jsonFiles = getJsonFiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Delete JSON Data</title>
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

        .table {
            color: #DCD7C9;
        }

        .table th {
            background-color: #2C3930;
            border-color: #A27B5C;
        }

        .table td {
            border-color: #A27B5C;
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

        .file-size {
            font-size: 0.9em;
            color: #A27B5C;
        }

        .file-date {
            font-size: 0.9em;
            color: #A27B5C;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow p-4 rounded">
                <h2 class="text-center mb-4">🗑️ Delete JSON Data Files</h2>

                <?php if (isset($message)) echo $message; ?>

                <?php if (empty($jsonFiles)): ?>
                    <div class="alert alert-warning">
                        📁 No JSON files found in the data directory.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>📄 File Name</th>
                                    <th>📊 Size</th>
                                    <th>📅 Last Modified</th>
                                    <th>⚡ Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jsonFiles as $file): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($file['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="file-size"><?= number_format($file['size'] / 1024, 2) ?> KB</span>
                                        </td>
                                        <td>
                                            <span class="file-date"><?= $file['date'] ?></span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete <?= htmlspecialchars($file['name']) ?>? This action cannot be undone.')">
                                                <button type="submit" name="delete_file" value="<?= htmlspecialchars($file['name']) ?>" class="btn btn-danger btn-sm">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="admin_dashboard.php" class="btn btn-secondary">🏠 Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html> 