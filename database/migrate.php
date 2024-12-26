<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Logger.php';

class DatabaseMigration {
    private $db;
    private $migrationsPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/migrations';
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }
    
    public function migrate() {
        // Get all migration files
        $migrations = glob($this->migrationsPath . '/*.sql');
        sort($migrations); // Sort by filename
        
        foreach ($migrations as $migration) {
            $migrationName = basename($migration);
            
            // Check if migration was already executed
            $executed = $this->db->fetch(
                "SELECT * FROM migrations WHERE migration = ?",
                [$migrationName]
            );
            
            if (!$executed) {
                try {
                    // Read and execute migration file
                    $sql = file_get_contents($migration);
                    $this->db->beginTransaction();
                    
                    // Split SQL file into individual statements
                    $statements = array_filter(
                        array_map('trim', explode(';', $sql)),
                        'strlen'
                    );
                    
                    foreach ($statements as $statement) {
                        $this->db->query($statement);
                    }
                    
                    // Record migration
                    $this->db->insert('migrations', ['migration' => $migrationName]);
                    
                    $this->db->commit();
                    echo "Executed migration: $migrationName\n";
                    Logger::info("Executed migration: $migrationName");
                } catch (Exception $e) {
                    $this->db->rollback();
                    echo "Error executing migration $migrationName: " . $e->getMessage() . "\n";
                    Logger::error("Migration failed: $migrationName", ['error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                echo "Skipping migration $migrationName (already executed)\n";
            }
        }
        
        echo "All migrations completed successfully!\n";
    }
    
    public function backup() {
        $backupFile = __DIR__ . '/backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupDir = dirname($backupFile);
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        // Get database credentials from config
        $command = sprintf(
            'mysqldump -h %s -u %s %s %s > %s',
            DB_HOST,
            DB_USER,
            DB_PASS ? '-p' . DB_PASS : '',
            DB_NAME,
            $backupFile
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "Database backup created successfully: $backupFile\n";
            Logger::info("Database backup created: $backupFile");
            return true;
        } else {
            echo "Error creating database backup\n";
            Logger::error("Database backup failed", ['output' => $output]);
            return false;
        }
    }
}

// Run migrations
try {
    $migration = new DatabaseMigration();
    
    // Create backup before migrating
    if ($migration->backup()) {
        $migration->migrate();
    } else {
        echo "Migration aborted due to backup failure\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
