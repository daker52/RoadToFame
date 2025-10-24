<?php

namespace WastelandDominion\Models;

use WastelandDominion\App;
use WastelandDominion\Database;

abstract class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;
    
    public function __construct()
    {
        $this->db = App::getInstance()->getDatabase();
    }
    
    public function find(int $id): ?array
    {
        $result = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
        
        return $result ? $this->processResult($result) : null;
    }
    
    public function findBy(string $column, $value): ?array
    {
        $result = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
        
        return $result ? $this->processResult($result) : null;
    }
    
    public function findAll(array $conditions = [], string $orderBy = null, int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $results = $this->db->fetchAll($sql, $params);
        
        return array_map([$this, 'processResult'], $results);
    }
    
    public function create(array $data): int
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert($this->table, $filteredData);
    }
    
    public function update(int $id, array $data): bool
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $rowsAffected = $this->db->update(
            $this->table,
            $filteredData,
            "{$this->primaryKey} = ?",
            [$id]
        );
        
        return $rowsAffected > 0;
    }
    
    public function delete(int $id): bool
    {
        $rowsAffected = $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
        
        return $rowsAffected > 0;
    }
    
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $result = $this->db->fetch($sql, $params);
        return (int)$result['count'];
    }
    
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }
    
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = [], string $orderBy = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $params);
        $totalCount = $this->count($conditions);
        
        return [
            'data' => array_map([$this, 'processResult'], $results),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount)
            ]
        ];
    }
    
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    protected function processResult(array $result): array
    {
        // Remove hidden fields
        foreach ($this->hidden as $hiddenField) {
            unset($result[$hiddenField]);
        }
        
        return $result;
    }
    
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->db->commit();
    }
    
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }
    
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTable(): string
    {
        return $this->table;
    }
}