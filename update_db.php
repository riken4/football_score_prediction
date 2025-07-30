 <?php
require 'config.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('update_database.sql');
    
    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database updated successfully!<br>";
    echo "<a href='dashboard.php'>Return to Dashboard</a>";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?> 