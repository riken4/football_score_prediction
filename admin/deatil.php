<?php
require '../config.php'; // Include your config with $pdo

// Prepare the SQL JOIN query
$sql = "
    SELECT 
        prediction_history.*, 
        tbl_user.username 
    FROM 
        prediction_history 
    JOIN 
        tbl_user 
    ON 
        prediction_history.username = tbl_user.id
";

// Execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch all results
$results = $stmt->fetchAll();

if ($results) {
    foreach ($results as $row) {
        echo "Username: " . htmlspecialchars($row['username']) . "<br>";
        echo "Match ID: " . htmlspecialchars($row['match_id']) . "<br>";
        echo "Prediction: " . htmlspecialchars($row['prediction']) . "<br><br>";
    }
} else {
    echo "No prediction history found.";
}
?>
