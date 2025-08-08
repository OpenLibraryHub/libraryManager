<?php
/**
 * Base Model Class
 * 
 * Provides common database operations for all models
 */

namespace App\Models;

use Config\Database;
use Exception;

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->queryOne($sql, 'i', [$id]);
    }
    
    /**
     * Find record by column
     */
    public function findBy(string $column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1";
        $type = is_int($value) ? 'i' : 's';
        return $this->db->queryOne($sql, $type, [$value]);
    }
    
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): array {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get records with conditions
     */
    public function where(array $conditions, array $columns = ['*']): array {
        $cols = implode(', ', $columns);
        $whereClause = [];
        $types = '';
        $values = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $types .= is_int($value) ? 'i' : 's';
            $values[] = $value;
        }
        
        $where = implode(' AND ', $whereClause);
        $sql = "SELECT {$cols} FROM {$this->table} WHERE {$where}";
        
        return $this->db->query($sql, $types, $values) ?: [];
    }
    
    /**
     * Create new record
     */
    public function create(array $data) {
        // Filter only fillable fields
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            throw new Exception("No fillable data provided");
        }
        
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $columnList = implode(', ', $columns);
        $placeholderList = implode(', ', $placeholders);
        
        $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES ({$placeholderList})";
        
        // Build types string
        $types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $result = $this->db->query($sql, $types, $values);
        
        if ($result !== false) {
            return $this->find($this->db->lastInsertId());
        }
        
        return false;
    }
    
    /**
     * Update record
     */
    public function update($id, array $data): bool {
        // Filter only fillable fields
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            throw new Exception("No fillable data provided");
        }
        
        $setClause = [];
        $types = '';
        $values = [];
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        // Add ID to values and types
        $values[] = $id;
        $types .= 'i';
        
        $set = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        
        $result = $this->db->query($sql, $types, $values);
        
        // Consider successful even if no rows were changed (values identical)
        return $result !== false;
    }
    
    /**
     * Delete record
     */
    public function delete($id): bool {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->query($sql, 'i', [$id]);
        
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Count records
     */
    public function count(array $conditions = []): int {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->queryOne($sql);
        } else {
            $whereClause = [];
            $types = '';
            $values = [];
            
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $types .= is_int($value) ? 'i' : 's';
                $values[] = $value;
            }
            
            $where = implode(' AND ', $whereClause);
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
            $result = $this->db->queryOne($sql, $types, $values);
        }
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Check if record exists
     */
    public function exists($id): bool {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$id]);
        return $result !== null;
    }
    
    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = []): array {
        $offset = ($page - 1) * $perPage;
        
        if (empty($conditions)) {
            $sql = "SELECT * FROM {$this->table} LIMIT ? OFFSET ?";
            $data = $this->db->query($sql, 'ii', [$perPage, $offset]) ?: [];
            $total = $this->count();
        } else {
            $whereClause = [];
            $types = '';
            $values = [];
            
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $types .= is_int($value) ? 'i' : 's';
                $values[] = $value;
            }
            
            $where = implode(' AND ', $whereClause);
            $values[] = $perPage;
            $values[] = $offset;
            $types .= 'ii';
            
            $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT ? OFFSET ?";
            $data = $this->db->query($sql, $types, $values) ?: [];
            $total = $this->count($conditions);
        }
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Filter fillable fields
     */
    protected function filterFillable(array $data): array {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide sensitive fields
     */
    public function hideFields(array $data): array {
        if (empty($this->hidden)) {
            return $data;
        }
        
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
    
    /**
     * Begin database transaction
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit database transaction
     */
    public function commit(): bool {
        return $this->db->commit();
    }
    
    /**
     * Rollback database transaction
     */
    public function rollback(): bool {
        return $this->db->rollback();
    }
    
    /**
     * Execute raw query (use with caution)
     */
    protected function raw(string $sql, string $types = '', array $params = []) {
        return $this->db->query($sql, $types, $params);
    }
}
