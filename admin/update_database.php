<?php
require_once '../config.php';

try {
    // Add new columns
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS status ENUM('active', 'banned') NOT NULL DEFAULT 'active'
    ");
    
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ");
    
    $pdo->exec("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL
    ");
    
    // Check if we need to migrate existing users from a different table
    $stmt = $pdo->query("SHOW TABLES LIKE 'users_old'");
    if ($stmt->rowCount() > 0) {
        // Migrate data from old table if exists
        $pdo->exec("
            INSERT IGNORE INTO users (username, email, password)
            SELECT username, email, password FROM users_old
        ");
    }
    
    echo "Database structure updated successfully!";
    echo "<br><a href='user_management.php'>Return to User Management</a>";
    
} catch (PDOException $e) {
    die("Database update failed: " . $e->getMessage());
} 