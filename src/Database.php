<?php

namespace WastelandDominion;

use PDO;
use PDOException;

class Database
{
    private $connection;
    private $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['name']};charset={$this->config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}"
            ];
            
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return (int)$this->connection->lastInsertId();
    }
    
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }
    
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
    
    public function tableExists(string $tableName): bool
    {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->fetch($sql, ['table' => $tableName]);
        return !empty($result);
    }
    
    public function createDatabase(): void
    {
        try {
            $tempDsn = "mysql:host={$this->config['host']};port={$this->config['port']};charset={$this->config['charset']}";
            $tempConnection = new PDO($tempDsn, $this->config['user'], $this->config['pass']);
            
            $dbName = $this->config['name'];
            $tempConnection->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            throw new \Exception("Failed to create database: " . $e->getMessage());
        }
    }
}