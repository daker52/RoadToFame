-- Combat and Enemies System
CREATE TABLE enemies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('wasteland_rats', 'raider_gangs', 'mutant_beasts', 'tech_horrors', 'legendary_bosses') NOT NULL,
    level INT UNSIGNED NOT NULL,
    
    -- Combat Stats
    health INT UNSIGNED NOT NULL,
    attack INT UNSIGNED NOT NULL,
    defense INT UNSIGNED NOT NULL,
    agility INT UNSIGNED NOT NULL,
    
    -- Special abilities
    special_abilities JSON,
    
    -- Loot tables
    loot_table JSON,
    experience_reward INT UNSIGNED DEFAULT 0,
    caps_reward_min INT UNSIGNED DEFAULT 0,
    caps_reward_max INT UNSIGNED DEFAULT 0,
    
    -- Visual and description
    description TEXT,
    avatar_path VARCHAR(255),
    
    -- Spawn data
    spawn_cities JSON, -- Which cities this enemy can appear in
    spawn_chance DECIMAL(5,2) DEFAULT 100.00,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_level (level),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Combat logs for battles
CREATE TABLE combat_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    enemy_id INT UNSIGNED NOT NULL,
    quest_instance_id INT UNSIGNED NULL,
    
    -- Combat result
    result ENUM('victory', 'defeat', 'flee') NOT NULL,
    
    -- Combat stats
    player_health_start INT UNSIGNED NOT NULL,
    player_health_end INT UNSIGNED NOT NULL,
    enemy_health_start INT UNSIGNED NOT NULL,
    enemy_health_end INT UNSIGNED NOT NULL,
    
    -- Damage dealt
    total_damage_dealt INT UNSIGNED DEFAULT 0,
    total_damage_taken INT UNSIGNED DEFAULT 0,
    
    -- Rewards (if victory)
    experience_gained INT UNSIGNED DEFAULT 0,
    caps_gained INT UNSIGNED DEFAULT 0,
    items_gained JSON,
    
    -- Combat log data
    combat_events JSON, -- Detailed turn-by-turn log
    
    duration_seconds INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (enemy_id) REFERENCES enemies(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_instance_id) REFERENCES quest_instances(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_enemy_id (enemy_id),
    INDEX idx_result (result),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;