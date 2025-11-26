-- Create meal_plan_completions table for tracking AI meal plan completions

CREATE TABLE IF NOT EXISTS meal_plan_completions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meals_data JSON NOT NULL COMMENT 'Stores the complete meal plan data',
    total_calories INT DEFAULT 0,
    total_protein INT DEFAULT 0,
    total_carbs INT DEFAULT 0,
    total_fats INT DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE meal_plan_completions COMMENT = 'Tracks AI-generated meal plan completions';
