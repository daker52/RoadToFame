-- Create admin user migration
-- This should be run after the initial user table is created

INSERT INTO users (username, email, password_hash, is_admin, is_banned, created_at) 
VALUES (
    'admin', 
    'admin@wasteland-dominion.com', 
    '$2y$10$VZJ8TT8HdcCxJzP7Jd/6C.rJ0sJj0P1MQF2h3dGvGhZ4h7kL9a5bW', -- password: admin123
    1, 
    0, 
    NOW()
);

-- Update user to have a profile
INSERT INTO user_profiles (
    user_id, 
    level, 
    experience, 
    strength, 
    agility, 
    intelligence, 
    endurance, 
    luck, 
    current_health, 
    max_health, 
    current_energy, 
    max_energy, 
    last_location_id
) VALUES (
    (SELECT id FROM users WHERE username = 'admin' LIMIT 1),
    1,
    0,
    10,
    10,
    10,
    10,
    10,
    100,
    100,
    100,
    100,
    1
);