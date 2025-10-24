-- Items and Equipment System
CREATE TABLE items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('weapon_melee', 'weapon_ranged', 'weapon_heavy', 'armor_light', 'armor_medium', 'armor_heavy', 'consumable', 'mod', 'material', 'misc') NOT NULL,
    rarity ENUM('common', 'uncommon', 'rare', 'epic', 'legendary') DEFAULT 'common',
    
    -- Base stats
    base_stats JSON, -- damage, defense, etc.
    
    -- Requirements
    level_requirement INT UNSIGNED DEFAULT 1,
    stat_requirements JSON,
    
    -- Item properties
    durability INT UNSIGNED DEFAULT 100,
    max_stack INT UNSIGNED DEFAULT 1,
    market_value INT UNSIGNED DEFAULT 0,
    
    -- Description and flavor
    description TEXT,
    flavor_text TEXT,
    
    -- Visual
    icon_path VARCHAR(255),
    model_data JSON,
    
    is_tradeable BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_rarity (rarity),
    INDEX idx_level_req (level_requirement),
    INDEX idx_active (is_active),
    INDEX idx_tradeable (is_tradeable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Player inventory
CREATE TABLE user_inventory (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    durability INT UNSIGNED DEFAULT 100,
    
    -- Equipment slots
    equipped_slot ENUM('weapon_primary', 'weapon_secondary', 'armor_head', 'armor_chest', 'armor_legs', 'armor_feet', 'armor_hands', 'accessory_1', 'accessory_2') NULL,
    
    -- Item modifications
    modifications JSON,
    
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_item_id (item_id),
    INDEX idx_equipped (equipped_slot),
    UNIQUE KEY unique_equipped_slot (user_id, equipped_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;