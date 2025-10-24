-- Player profiles and game state
CREATE TABLE user_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL,
    profession_id INT UNSIGNED NOT NULL,
    level INT UNSIGNED DEFAULT 1,
    experience INT UNSIGNED DEFAULT 0,
    current_city_id INT UNSIGNED NOT NULL,
    current_location_id INT UNSIGNED NULL,
    
    -- Player Statistics
    strength INT UNSIGNED DEFAULT 10,
    agility INT UNSIGNED DEFAULT 10,
    intelligence INT UNSIGNED DEFAULT 10,
    endurance INT UNSIGNED DEFAULT 10,
    luck INT UNSIGNED DEFAULT 10,
    
    -- Health and Energy
    current_health INT UNSIGNED DEFAULT 100,
    max_health INT UNSIGNED DEFAULT 100,
    current_energy INT UNSIGNED DEFAULT 100,
    max_energy INT UNSIGNED DEFAULT 100,
    energy_regen_at TIMESTAMP NULL,
    
    -- Avatar and customization
    avatar_data JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    FOREIGN KEY (current_city_id) REFERENCES cities(id),
    FOREIGN KEY (current_location_id) REFERENCES locations(id),
    
    INDEX idx_user_id (user_id),
    INDEX idx_level (level),
    INDEX idx_profession (profession_id),
    INDEX idx_location (current_city_id, current_location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currency accounts
CREATE TABLE currency_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    bottle_caps INT UNSIGNED DEFAULT 100,
    diamonds INT UNSIGNED DEFAULT 0,
    energy_cells INT UNSIGNED DEFAULT 0,
    scrap_metal INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;