-- Add new columns to existing users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS status ENUM('active', 'banned') NOT NULL DEFAULT 'active',
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL;

-- Drop primary key if exists and update the id column
ALTER TABLE users
DROP PRIMARY KEY,
MODIFY id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Rename username column if it doesn't match (this won't affect data)
ALTER TABLE users CHANGE COLUMN UserName username varchar(255) NOT NULL;

-- Update email column if it doesn't match (this won't affect data)
ALTER TABLE users CHANGE COLUMN Email email varchar(255) NOT NULL;

-- Create indexes if they don't exist
SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE()
        AND table_name = 'users' 
        AND index_name = 'idx_username'
    ),
    'SELECT "Index idx_username already exists"',
    'CREATE INDEX idx_username ON users(username)'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE()
        AND table_name = 'users' 
        AND index_name = 'idx_email'
    ),
    'SELECT "Index idx_email already exists"',
    'CREATE INDEX idx_email ON users(email)'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE()
        AND table_name = 'users' 
        AND index_name = 'idx_status'
    ),
    'SELECT "Index idx_status already exists"',
    'CREATE INDEX idx_status ON users(status)'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'banned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
); 