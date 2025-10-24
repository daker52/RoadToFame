<?php

/**
 * Database Seeder for Wasteland Dominion
 * This script populates database with initial game data
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WastelandDominion\App;

class DatabaseSeeder
{
    private $db;
    private $config;
    
    public function __construct()
    {
        $app = App::getInstance();
        $this->db = $app->getDatabase();
        $this->config = $app->getConfig();
    }
    
    public function run(): void
    {
        echo "🌱 Starting Wasteland Dominion Database Seeding...\n\n";
        
        try {
            $this->seedAdminUser();
            $this->seedLocations();
            $this->seedQuests();
            $this->seedItems();
            $this->seedEnemies();
            $this->seedRecipes();
            
            echo "\n✅ All seeding completed successfully!\n";
            echo "🎮 Game data is ready for Wasteland Dominion!\n";
            
        } catch (Exception $e) {
            echo "\n❌ Seeding failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function seedAdminUser(): void
    {
        echo "👤 Seeding admin user...\n";
        
        $stmt = $this->db->pdo()->prepare("
            SELECT COUNT(*) FROM users WHERE username = 'admin'
        ");
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            echo "   ⏭️ Admin user already exists\n";
            return;
        }
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $this->db->pdo()->prepare("
            INSERT INTO users (username, email, password_hash, is_admin, is_active, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'admin',
            'admin@wasteland-dominion.com',
            $password,
            1, // is_admin
            1  // is_active
        ]);
        
        echo "   ✅ Admin user created (username: admin, password: admin123)\n";
    }
    
    private function seedLocations(): void
    {
        echo "🗺️ Seeding world locations...\n";
        
        $locations = [
            [
                'name' => 'Vault City',
                'description' => 'Bezpečné město postavené v bývalém úkrytu. Zde začíná vaše cesta.',
                'x' => 50,
                'y' => 50,
                'danger_level' => 1,
                'travel_cost' => 0,
                'resources' => json_encode(['water', 'food']),
                'is_safe_zone' => 1
            ],
            [
                'name' => 'Ruined Mall',
                'description' => 'Zbytky nákupního centra plné užitečných věcí a nebezpečí.',
                'x' => 30,
                'y' => 70,
                'danger_level' => 2,
                'travel_cost' => 10,
                'resources' => json_encode(['scrap', 'electronics']),
                'is_safe_zone' => 0
            ],
            [
                'name' => 'Industrial Zone',
                'description' => 'Opuštěná tovární čtvrť s cennými materiály.',
                'x' => 80,
                'y' => 30,
                'danger_level' => 3,
                'travel_cost' => 15,
                'resources' => json_encode(['metal', 'chemicals']),
                'is_safe_zone' => 0
            ],
            [
                'name' => 'Desert Outpost',
                'description' => 'Osamělá zastávka na okraji pouště.',
                'x' => 20,
                'y' => 20,
                'danger_level' => 2,
                'travel_cost' => 12,
                'resources' => json_encode(['fuel', 'ammunition']),
                'is_safe_zone' => 0
            ],
            [
                'name' => 'Toxic Swamp',
                'description' => 'Nebezpečná bažina plná mutantů a radiace.',
                'x' => 10,
                'y' => 80,
                'danger_level' => 4,
                'travel_cost' => 20,
                'resources' => json_encode(['rare_minerals', 'toxic_waste']),
                'is_safe_zone' => 0
            ],
            [
                'name' => 'Military Base',
                'description' => 'Opuštěná vojenská základna s pokročilou výzbrojí.',
                'x' => 90,
                'y' => 90,
                'danger_level' => 5,
                'travel_cost' => 25,
                'resources' => json_encode(['advanced_weapons', 'military_tech']),
                'is_safe_zone' => 0
            ]
        ];
        
        foreach ($locations as $location) {
            $stmt = $this->db->pdo()->prepare("
                INSERT IGNORE INTO world_locations 
                (name, description, x, y, danger_level, travel_cost, resources, is_safe_zone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $location['name'],
                $location['description'],
                $location['x'],
                $location['y'],
                $location['danger_level'],
                $location['travel_cost'],
                $location['resources'],
                $location['is_safe_zone']
            ]);
        }
        
        echo "   ✅ " . count($locations) . " locations seeded\n";
    }
    
    private function seedItems(): void
    {
        echo "🎒 Seeding items...\n";
        
        $items = [
            // Weapons
            ['Rusty Knife', 'weapon', 'Základní nůž pro boj zblízka', 5, 0, 10, json_encode(['damage' => 5])],
            ['Pipe Rifle', 'weapon', 'Improvizovaná puška', 50, 0, 25, json_encode(['damage' => 15, 'range' => 'long'])],
            ['Plasma Pistol', 'weapon', 'Pokročilá energetická zbraň', 200, 0, 15, json_encode(['damage' => 25, 'energy' => true])],
            
            // Armor
            ['Leather Jacket', 'armor', 'Základní ochrana', 25, 0, 5, json_encode(['armor' => 3])],
            ['Combat Armor', 'armor', 'Vojenské brnění', 150, 0, 20, json_encode(['armor' => 15])],
            ['Power Armor', 'armor', 'Nejlepší ochrana', 1000, 0, 50, json_encode(['armor' => 30, 'strength' => 5])],
            
            // Consumables
            ['Rad-Away', 'consumable', 'Odstraňuje radiaci', 20, 1, 1, json_encode(['heal_radiation' => 50])],
            ['Stimpak', 'consumable', 'Rychle léčí zranění', 15, 1, 1, json_encode(['heal_health' => 30])],
            ['Nuka Cola', 'consumable', 'Obnovuje energii', 5, 1, 1, json_encode(['restore_energy' => 20])],
            
            // Resources
            ['Scrap Metal', 'resource', 'Základní materiál pro crafting', 2, 1, 1, json_encode(['crafting' => true])],
            ['Electronic Parts', 'resource', 'Elektronické součástky', 10, 1, 1, json_encode(['crafting' => true])],
            ['Pure Water', 'resource', 'Čistá voda', 8, 1, 1, json_encode(['crafting' => true])]
        ];
        
        foreach ($items as $item) {
            $stmt = $this->db->pdo()->prepare("
                INSERT IGNORE INTO items 
                (name, type, description, value, stackable, weight, stats) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute($item);
        }
        
        echo "   ✅ " . count($items) . " items seeded\n";
    }
    
    private function seedQuests(): void
    {
        echo "🎯 Seeding quests...\n";
        
        $quests = [
            [
                'title' => 'Welcome to the Wasteland',
                'description' => 'Seznamte se s ovládáním hry a prozkoumejte Vault City.',
                'type' => 'tutorial',
                'level_requirement' => 1,
                'location_id' => 1,
                'experience_reward' => 50,
                'caps_reward' => 25,
                'objectives' => json_encode([
                    'Otevřte inventář',
                    'Navštivte mapu světa',
                    'Promluvte si s NPC'
                ])
            ],
            [
                'title' => 'Scrap Hunt',
                'description' => 'Najděte 5 kusů kovového šrotu v Ruined Mall.',
                'type' => 'collection',
                'level_requirement' => 2,
                'location_id' => 2,
                'experience_reward' => 100,
                'caps_reward' => 50,
                'objectives' => json_encode([
                    'Cestujte do Ruined Mall',
                    'Najděte 5x Scrap Metal',
                    'Vraťte se do Vault City'
                ])
            ],
            [
                'title' => 'Raider Problem',
                'description' => 'Eliminujte skupinu nájezdníků ohrožujících obchodní cesty.',
                'type' => 'combat',
                'level_requirement' => 3,
                'location_id' => 4,
                'experience_reward' => 200,
                'caps_reward' => 100,
                'objectives' => json_encode([
                    'Poražte 3 nájezdníky',
                    'Získejte jejich zbraně',
                    'Nahlašte úspěch strážci'
                ])
            ]
        ];
        
        foreach ($quests as $quest) {
            $stmt = $this->db->pdo()->prepare("
                INSERT IGNORE INTO quests 
                (title, description, type, level_requirement, location_id, experience_reward, caps_reward, objectives) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $quest['title'],
                $quest['description'],
                $quest['type'],
                $quest['level_requirement'],
                $quest['location_id'],
                $quest['experience_reward'],
                $quest['caps_reward'],
                $quest['objectives']
            ]);
        }
        
        echo "   ✅ " . count($quests) . " quests seeded\n";
    }
    
    private function seedEnemies(): void
    {
        echo "👹 Seeding enemies...\n";
        
        $enemies = [
            ['Rad Roach', 'creature', 1, 25, 5, 1, 2, 'Zmutovaný šváb'],
            ['Raider Scout', 'human', 2, 40, 8, 3, 5, 'Slabý nájezdník'],
            ['Feral Ghoul', 'undead', 3, 60, 12, 2, 8, 'Zběsilý ghoul'],
            ['Super Mutant', 'mutant', 5, 120, 20, 8, 3, 'Silný mutant'],
            ['Deathclaw', 'creature', 10, 300, 50, 15, 12, 'Nejnebezpečnější tvor']
        ];
        
        foreach ($enemies as $enemy) {
            $stmt = $this->db->pdo()->prepare("
                INSERT IGNORE INTO enemies 
                (name, type, level, health, damage, armor, speed, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute($enemy);
        }
        
        echo "   ✅ " . count($enemies) . " enemies seeded\n";
    }
    
    private function seedRecipes(): void
    {
        echo "🔧 Seeding crafting recipes...\n";
        
        $recipes = [
            [
                'name' => 'Improvised Weapon',
                'description' => 'Vyrobte základní zbraň z kovu a šrotu',
                'required_items' => json_encode([
                    'Scrap Metal' => 3,
                    'Electronic Parts' => 1
                ]),
                'result_item' => 'Pipe Rifle',
                'skill_requirement' => 2,
                'crafting_time' => 300 // 5 minut
            ],
            [
                'name' => 'Healing Potion',
                'description' => 'Vyrobte léčivý lektvar',
                'required_items' => json_encode([
                    'Pure Water' => 1,
                    'Rad-Away' => 1
                ]),
                'result_item' => 'Stimpak',
                'skill_requirement' => 1,
                'crafting_time' => 180 // 3 minuty
            ]
        ];
        
        foreach ($recipes as $recipe) {
            $stmt = $this->db->pdo()->prepare("
                INSERT IGNORE INTO crafting_recipes 
                (name, description, required_items, result_item, skill_requirement, crafting_time) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $recipe['name'],
                $recipe['description'],
                $recipe['required_items'],
                $recipe['result_item'],
                $recipe['skill_requirement'],
                $recipe['crafting_time']
            ]);
        }
        
        echo "   ✅ " . count($recipes) . " recipes seeded\n";
    }
    
    public function clear(): void
    {
        echo "🧹 Clearing all seeded data...\n";
        
        $tables = [
            'crafting_recipes',
            'enemies', 
            'quests',
            'items',
            'world_locations'
        ];
        
        foreach ($tables as $table) {
            $this->db->pdo()->exec("TRUNCATE TABLE {$table}");
            echo "   🗑️ Cleared {$table}\n";
        }
        
        echo "✅ All data cleared\n";
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $command = $argv[1] ?? 'seed';
    
    try {
        $seeder = new DatabaseSeeder();
        
        switch ($command) {
            case 'seed':
                $seeder->run();
                break;
                
            case 'clear':
                $seeder->clear();
                break;
                
            default:
                echo "Usage:\n";
                echo "  php seed.php seed  - Seed database with initial data\n";
                echo "  php seed.php clear - Clear all seeded data\n";
                exit(1);
        }
        
    } catch (Exception $e) {
        echo "💥 Seeding failed: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "<h1>🎮 Wasteland Dominion - Database Seeder</h1>";
    echo "<p>This script should be run from command line:</p>";
    echo "<pre>php database/seed.php</pre>";
}