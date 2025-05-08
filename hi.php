<?php
require 'config.php'; // Include your config with $pdo

// Prepare the SQL JOIN query
$sql = "
    SELECT 
        prediction_history.*, 
        tbl_user.UserName 
    FROM 
        prediction_history 
    JOIN 
        tbl_user 
    ON 
        prediction_history.h_id = tbl_user.id
";

// Execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch all results
$results = $stmt->fetchAll();

if ($results) {
    foreach ($results as $row) {
        echo  <tbody>
            <?php foreach ($predictions as $row) ?>
                <tr>
                <?php foreach ($predictions as $row): ?>
                    <td><?= $row['username'] ?></td>
                    <td><?= htmlspecialchars($row['teamA']) ?> vs <?= htmlspecialchars($row['teamB']) ?></td>
                    <td><?= htmlspecialchars($row['season']) ?></td>
                    <td><?= $row['winA'] ?>%</td>
                    <td><?= $row['draw'] ?>%</td>
                    <td><?= $row['winB'] ?>%</td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        echo "Match ID: " . htmlspecialchars($row['match_id']) . "<br>";
        echo "Prediction: " . htmlspecialchars($row['prediction']) . "<br><br>";
        
    }
} else {
    echo "No prediction history found.";
}
?>
