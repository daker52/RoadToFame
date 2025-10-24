<?php

namespace WastelandDominion;

class Migration
{
    private $db;
    private $migrationsPath;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->migrationsPath = __DIR__ . '/../database/migrations/';
    }
    
    public function run(): void
    {
        echo "🔄 Starting database migrations...\n";
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
        
        // Get all migration files
        $migrationFiles = $this->getMigrationFiles();
        
        // Get completed migrations
        $completedMigrations = $this->getCompletedMigrations();
        
        $executed = 0;
        
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $completedMigrations)) {
                echo "⚡ Executing migration: {$migrationName}\n";
                
                try {
                    $this->executeMigration($file);
                    $this->recordMigration($migrationName);
                    $executed++;
                    echo "✅ Migration completed: {$migrationName}\n";
                } catch (\Exception $e) {
                    echo "❌ Migration failed: {$migrationName}\n";
                    echo "Error: " . $e->getMessage() . "\n";
                    break;
                }
            }
        }
        
        if ($executed === 0) {
            echo "✨ No new migrations to run\n";
        } else {
            echo "🎉 Executed {$executed} migrations successfully\n";
        }
    }
    
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->query($sql);
    }
    
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }
        
        $files = glob($this->migrationsPath . '*.sql');
        sort($files);
        
        return $files;
    }
    
    private function getCompletedMigrations(): array
    {
        $result = $this->db->fetchAll("SELECT migration FROM migrations ORDER BY executed_at");
        return array_column($result, 'migration');
    }
    
    private function executeMigration(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        
        if (empty($sql)) {
            throw new \Exception("Migration file is empty: {$filePath}");
        }
        
        // Split SQL file by semicolons (simple approach)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($statement) {
                return !empty($statement) && !preg_match('/^\s*--/', $statement);
            }
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->query($statement);
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function recordMigration(string $migrationName): void
    {
        $this->db->insert('migrations', [
            'migration' => $migrationName
        ]);
    }
    
    public function reset(): void
    {
        echo "🔥 Resetting database...\n";
        
        // Get all tables
        $tables = $this->db->fetchAll("SHOW TABLES");
        $tableColumn = array_keys($tables[0])[0] ?? 'Tables_in_' . $this->db->getConnection()->query('SELECT DATABASE()')->fetchColumn();
        
        // Disable foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            $tableName = $table[$tableColumn];
            echo "🗑️ Dropping table: {$tableName}\n";
            $this->db->query("DROP TABLE IF EXISTS `{$tableName}`");
        }
        
        // Re-enable foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "✅ Database reset complete\n";
    }
}