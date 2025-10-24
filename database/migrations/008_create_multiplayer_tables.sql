-- Multiplayer Features: Guilds and Trading
CREATE TABLE guilds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    leader_id INT UNSIGNED NOT NULL,
    member_limit INT UNSIGNED DEFAULT 50,
    level INT UNSIGNED DEFAULT 1,
    experience INT UNSIGNED DEFAULT 0,
    guild_caps INT UNSIGNED DEFAULT 0,
    
    -- Guild settings
    is_public BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE,
    
    -- Guild hall/base
    guild_hall_city_id INT UNSIGNED NULL,
    guild_hall_data JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (guild_hall_city_id) REFERENCES cities(id) ON DELETE SET NULL,
    
    INDEX idx_name (name),
    INDEX idx_leader_id (leader_id),
    INDEX idx_public (is_public),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guild_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guild_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rank ENUM('member', 'officer', 'leader') DEFAULT 'member',
    contribution_points INT UNSIGNED DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_guild_member (guild_id, user_id),
    INDEX idx_guild_id (guild_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rank (rank)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trading system
CREATE TABLE player_trades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    initiator_id INT UNSIGNED NOT NULL,
    recipient_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    
    -- Trade items and currencies
    initiator_items JSON, -- [{item_id, quantity, durability}]
    initiator_caps INT UNSIGNED DEFAULT 0,
    initiator_diamonds INT UNSIGNED DEFAULT 0,
    
    recipient_items JSON,
    recipient_caps INT UNSIGNED DEFAULT 0,
    recipient_diamonds INT UNSIGNED DEFAULT 0,
    
    -- Trade log
    trade_log JSON,
    
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (initiator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_initiator_id (initiator_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;