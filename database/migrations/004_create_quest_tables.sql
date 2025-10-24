-- Quest System Tables
CREATE TABLE quests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    type ENUM('kill', 'fetch', 'delivery', 'exploration') NOT NULL,
    difficulty ENUM('quick', 'standard', 'epic', 'legendary') NOT NULL,
    level_requirement INT UNSIGNED DEFAULT 1,
    duration_seconds INT UNSIGNED NOT NULL,
    
    -- Requirements
    prerequisite_quest_id INT UNSIGNED NULL,
    required_items JSON,
    required_stats JSON,
    
    -- Rewards
    experience_reward INT UNSIGNED DEFAULT 0,
    caps_reward INT UNSIGNED DEFAULT 0,
    item_rewards JSON,
    
    -- Quest data
    target_data JSON, -- enemy_id for kill quests, item_id for fetch, etc.
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_quest_id) REFERENCES quests(id) ON DELETE SET NULL,
    
    INDEX idx_city_id (city_id),
    INDEX idx_type (type),
    INDEX idx_difficulty (difficulty),
    INDEX idx_level_req (level_requirement),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Active quest instances for players
CREATE TABLE quest_instances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    quest_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'completed', 'failed', 'abandoned') DEFAULT 'active',
    progress JSON, -- Store quest progress data
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    deadline_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_quest_id (quest_id),
    INDEX idx_status (status),
    INDEX idx_deadline (deadline_at),
    UNIQUE KEY unique_active_quest (user_id, quest_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;