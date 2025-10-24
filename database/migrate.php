<?php

require_once __DIR__ . '/../vendor/autoload.php';

use WastelandDominion\App;
use WastelandDominion\Migration;

try {
    echo "🎮 Wasteland Dominion Database Migration Tool\n";
    echo "==========================================\n\n";
    
    // Initialize app
    $app = App::getInstance();
    $db = $app->getDatabase();
    
    // Create database if it doesn't exist
    try {
        $db->createDatabase();
        echo "✅ Database created/verified\n\n";
    } catch (Exception $e) {
        echo "⚠️ Database creation warning: " . $e->getMessage() . "\n\n";
    }
    
    // Run migrations
    $migration = new Migration($db);
    
    // Check command line arguments
    $command = $argv[1] ?? 'run';
    
    switch ($command) {
        case 'reset':
            echo "🔥 WARNING: This will delete all data!\n";
            echo "Continue? (y/N): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            
            if (trim(strtolower($line)) === 'y') {
                $migration->reset();
                $migration->run();
            } else {
                echo "❌ Migration reset cancelled\n";
            }
            break;
            
        case 'run':
        default:
            $migration->run();
            break;
    }
    
    echo "\n🎉 Migration process completed!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}