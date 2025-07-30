<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
// Load all JSON files in the /data directory
$jsonFiles = glob('data/*.json');

// Get selected file and season from URL
$selectedFile = $_GET['file'] ?? '';
$selectedSeason = $_GET['season'] ?? '';

// Get basename list for dropdown
$fileOptions = array_map('basename', $jsonFiles);

// Initialize data
$data = [];
$seasons = [];

if ($selectedFile && in_array("data/$selectedFile", $jsonFiles)) {
    $jsonContent = file_get_contents("data/$selectedFile");
    $data = json_decode($jsonContent, true);

    // Filter seasons
    $seasons = array_unique(array_column($data, 'season'));
    sort($seasons);

    // Filter by season if selected
    if ($selectedSeason) {
        $data = array_filter($data, fn($match) => $match['season'] === $selectedSeason);
    }

    // Collect all unique keys
    $allKeys = [];
    foreach ($data as $row) {
        $allKeys = array_merge($allKeys, array_keys($row));
    }
    $allKeys = array_unique($allKeys);
    sort($allKeys);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Football Match Data Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #2C3930;
        color: #DCD7C9;
        font-family: 'Segoe UI', sans-serif;
    }

    .card, .alert {
        background-color: #3F4E44;
        color: #DCD7C9;
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    .form-control, .form-select {
        background-color: #DCD7C9;
        color: #2C3930;
        border-radius: 10px;
    }

    .form-control:focus, .form-select:focus {
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

    .table {
        background-color: #2C3930;
        color: #DCD7C9;
        border-radius: 10px;
        overflow: hidden;
    }

    .table th {
        background-color:rgb(133, 101, 75);
        color: #fff;
        border: 1px solid #555;
       
    }

    .table td {
        background-color: #dcd7c9;
        border: 1px solid #555;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .table-striped tbody tr:nth-of-type(odd) td {
        background-color: #dcd7c9;
    }

    .alert-warning {
        background-color: #A27B5C;
        color: white;
        border: none;
    }

    h2 {
        font-weight: bold;
        color: #DCD7C9;
    }
</style>

</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4 text-center">📊 Football Match Data Viewer</h2>

    <!-- File Selector -->
    <form method="get" class="mb-3 d-flex flex-wrap align-items-center justify-content-center">
        <label for="file" class="form-label me-2">Filter Season:</label>
        <select name="file" id="file" class="form-select w-auto me-3" onchange="this.form.submit()">
            <option value="">-- Choose File --</option>
            <?php foreach ($fileOptions as $file): ?>
                <option value="<?= $file ?>" <?= $file === $selectedFile ? 'selected' : '' ?>>
                    <?= pathinfo($file, PATHINFO_FILENAME) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- <?php if ($selectedFile && $seasons): ?>
            <label for="season" class="form-label me-2">Filter Season:</label>
            <select name="season" id="season" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">All Seasons</option>
                <?php foreach ($seasons as $season): ?>
                    <option value="<?= $season ?>" <?= $season === $selectedSeason ? 'selected' : '' ?>>
                        <?= $season ?>
                    </option>
                <?php endforeach ?>
            </select>
        <?php endif; ?> -->
    </form>

    <!-- Table Display -->
    <?php if ($data): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($allKeys as $key): ?>
                            <th><?= htmlspecialchars($key) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($allKeys as $key): ?>
                                <td><?= isset($row[$key]) ? htmlspecialchars($row[$key]) : '-' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($selectedFile): ?>
        <div class="alert alert-warning mt-4">No data available for this file and season.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
