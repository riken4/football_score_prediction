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
        th, td { white-space: nowrap; font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4">⚽ Football Match Data Viewer</h2>

    <!-- File Selector -->
    <form method="get" class="mb-3">
        <label for="file" class="form-label">Select Season:</label>
        <select name="file" id="file" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            <option value="">-- Choose File --</option>
            <?php foreach ($fileOptions as $file): ?>
                <option value="<?= $file ?>" <?= $file === $selectedFile ? 'selected' : '' ?>>
    <?= pathinfo($file, PATHINFO_FILENAME) ?>
</option>

            <?php endforeach; ?>
        </select>

        <!-- Season Selector -->
        <!-- <?php if ($selectedFile && $seasons): ?>
            <label for="season" class="form-label ms-3">Filter Season:</label>
            <select name="season" id="season" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
                <option value="">All Seasons</option>
                <?php foreach ($seasons as $season): ?>
                    <option value="<?= $season ?>" <?= $season === $selectedSeason ? 'selected' : '' ?>>
                        <?= $season ?>
                    </option>
                <?php endforeach; ?>
            </select> -->
        <?php endif; ?>
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
        <div class="alert alert-warning">No data available for this file and season.</div>
    <?php endif; ?>
</div>
</body>
</html>
