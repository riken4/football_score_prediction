-- Drop the existing table if it exists
DROP TABLE IF EXISTS user_favourites;

-- Create the user_favourites table with support for multiple favorites and seasons
CREATE TABLE user_favourites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    season VARCHAR(10) NOT NULL DEFAULT '2012-13',
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_team_season (username, team_name, season)
);

-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create prediction_parameters table
CREATE TABLE IF NOT EXISTS prediction_parameters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parameter_name VARCHAR(50) NOT NULL,
    parameter_value FLOAT NOT NULL,
    description TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create prediction_history table
CREATE TABLE IF NOT EXISTS prediction_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_a VARCHAR(100) NOT NULL,
    team_b VARCHAR(100) NOT NULL,
    season VARCHAR(10) NOT NULL,
    predicted_win_a FLOAT NOT NULL,
    predicted_draw FLOAT NOT NULL,
    predicted_win_b FLOAT NOT NULL,
    actual_result ENUM('A', 'D', 'B') NOT NULL,
    prediction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accuracy_score FLOAT
);

-- Insert default prediction parameters
INSERT INTO prediction_parameters (parameter_name, parameter_value, description) VALUES
('home_advantage', 1.35, 'Advantage factor for home teams'),
('yellow_card_penalty', 0.02, 'Performance penalty per yellow card'),
('red_card_penalty', 0.08, 'Performance penalty per red card'),
('min_strength', 0.1, 'Minimum team strength value'),
('max_goals', 5, 'Maximum goals to consider in predictions'),
('form_weight', 0.3, 'Weight given to recent form');

-- Add indexes for better performance
CREATE INDEX idx_username ON user_favourites(username);
CREATE INDEX idx_team_name ON user_favourites(team_name);
CREATE INDEX idx_season ON user_favourites(season);
CREATE INDEX idx_prediction_teams ON prediction_history(team_a, team_b);
CREATE INDEX idx_prediction_season ON prediction_history(season);
CREATE INDEX idx_prediction_date ON prediction_history(prediction_date);

-- Add foreign key if users table exists (optional, uncomment if needed)
-- ALTER TABLE user_favourites
-- ADD CONSTRAINT fk_user_favourites_username
-- FOREIGN KEY (username) REFERENCES users(username)
-- ON DELETE CASCADE
-- ON UPDATE CASCADE; 