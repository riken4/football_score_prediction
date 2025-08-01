<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
?>
<?php
require_once '../config.php';

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$total_stmt = $pdo->query("SELECT COUNT(*) as total FROM prediction_history");
$total_records = $total_stmt->fetch()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page
$stmt = $pdo->prepare("SELECT * FROM prediction_history ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$predictions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Prediction History</title>
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
            background-color: rgb(133, 101, 75);
            color: #fff;
            border: 1px solid #555;
        }

        .table td {
            background-color: #dcd7c9;
            border: 1px solid #555;
            font-size: 0.85rem;
            white-space: nowrap;
            color: #2C3930;
        }

        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: #dcd7c9;
        }

        h1 {
            font-weight: bold;
            color: #DCD7C9;
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .page-link {
            background-color: #3F4E44;
            border-color: #A27B5C;
            color: #DCD7C9;
        }

        .page-link:hover {
            background-color: #A27B5C;
            border-color: #A27B5C;
            color: #fff;
        }

        .page-item.active .page-link {
            background-color: #A27B5C;
            border-color: #A27B5C;
        }

        .page-item.disabled .page-link {
            background-color: #2C3930;
            border-color: #555;
            color: #666;
        }

        .pagination-info {
            text-align: center;
            margin-bottom: 1rem;
            color: #DCD7C9;
        }
     
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container py-5">
    <h1 class="mb-4 text-center">📊 Prediction History</h1>
    
    <!-- Pagination Info -->
    <!-- <div class="pagination-info">
        Showing <?= $offset + 1 ?> to <?= min($offset + $records_per_page, $total_records) ?> of <?= $total_records ?> records
    </div> -->

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Teams</th>
                    <th>Season</th>
                    <th>Win A</th>
                    <th>Draw</th>
                    <th>Win B</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($predictions as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['teamA']) ?> vs <?= htmlspecialchars($row['teamB']) ?></td>
                        <td><?= htmlspecialchars($row['season']) ?></td>
                        <td><?= $row['winA'] ?>%</td>
                        <td><?= $row['draw'] ?>%</td>
                        <td><?= $row['winB'] ?>%</td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Prediction history pagination">
        <ul class="pagination">
            <!-- Previous button -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1">1</a>
                </li>
                <?php if ($start_page > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
                </li>
            <?php endif; ?>

            <!-- Next button -->
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
