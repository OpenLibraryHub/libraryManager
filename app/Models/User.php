<?php
/**
 * User Model
 * 
 * Handles user data and operations
 */

namespace App\Models;

use App\Helpers\Validator;

class User extends Model {
    protected string $table = 'users';
    protected string $primaryKey = 'id_number';
    
    protected array $fillable = [
        'user_key',
        'first_name',
        'last_name',
        'email',
        'id_number',
        'phone',
        'address',
        'sanctioned',
        'sanctioned_at',
        'created_at'
    ];
    
    /**
     * Find user by id number or key
     */
    public function findByIdentifier($identifier) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id_number = ? OR user_key = ? 
                LIMIT 1";
        
        // Determine type based on identifier
        $type = is_numeric($identifier) ? 'ii' : 'ss';
        
        return $this->db->queryOne($sql, $type, [$identifier, $identifier]);
    }
    
    /**
     * Search users (field values: all, id, first_name, last_name, email, key)
     */
    public function search(string $query, string $field = 'all'): array {
        $query = '%' . $this->db->escape($query) . '%';
        
        switch ($field) {
            case 'id':
                $sql = "SELECT * FROM {$this->table} WHERE id_number LIKE ?";
                break;
            case 'first_name':
                $sql = "SELECT * FROM {$this->table} WHERE first_name LIKE ?";
                break;
            case 'last_name':
                $sql = "SELECT * FROM {$this->table} WHERE last_name LIKE ?";
                break;
            case 'email':
                $sql = "SELECT * FROM {$this->table} WHERE email LIKE ?";
                break;
            case 'key':
                $sql = "SELECT * FROM {$this->table} WHERE user_key LIKE ?";
                break;
            default:
                $sql = "SELECT * FROM {$this->table} 
                        WHERE id_number LIKE ? 
                        OR first_name LIKE ? 
                        OR last_name LIKE ? 
                        OR email LIKE ? 
                        OR user_key LIKE ?";
                return $this->db->query($sql, 'sssss', [$query, $query, $query, $query, $query]) ?: [];
        }
        
        return $this->db->query($sql, 's', [$query]) ?: [];
    }
    
    /**
     * Get all users ordered by date
     */
    public function getAllOrdered(string $order = 'DESC'): array {
        $order = in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'DESC';
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at {$order}";
        return $this->db->query($sql) ?: [];
    }

    /**
     * Get a page of users with ordering
     */
    public function getPageOrdered(int $limit, int $offset, string $order = 'DESC', string $sortBy = 'created_at'): array {
        $order = in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'DESC';
        $allowed = ['created_at','first_name','last_name','email','id_number','user_key'];
        $sortBy = in_array($sortBy, $allowed, true) ? $sortBy : 'created_at';
        $sql = "SELECT * FROM {$this->table} ORDER BY {$sortBy} {$order} LIMIT ? OFFSET ?";
        return $this->db->query($sql, 'ii', [$limit, $offset]) ?: [];
    }
    
    /**
     * Check if user exists by email, id_number, or user_key
     */
    public function userExists($email, $idNumber, $userKey, $phone = null): bool {
        $sql = "SELECT 1 FROM {$this->table} 
                WHERE email = ? OR id_number = ? OR user_key = ?";
        
        $params = [$email, $idNumber, $userKey];
        $types = 'sii';
        
        // Solo considerar teléfono si viene con valor (>0)
        if ($phone !== null && (int)$phone > 0) {
            $sql .= " OR phone = ?";
            $params[] = $phone;
            $types .= 'i';
        }
        
        $sql .= " LIMIT 1";
        
        $result = $this->db->queryOne($sql, $types, $params);
        // null => no rows; array => found; false => query error (treat as not found)
        return is_array($result);
    }
    
    /**
     * Check if user is sanctioned
     */
    public function isSanctioned($idNumber): bool {
        $sql = "SELECT sanctioned FROM {$this->table} WHERE id_number = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$idNumber]);
        return $result && (int)$result['sanctioned'] === 1;
    }
    
    /**
     * Sanction user
     */
    public function sanction($idNumber): bool {
        $sql = "UPDATE {$this->table} 
                SET sanctioned = 1, sanctioned_at = NOW() 
                WHERE id_number = ?";
        
        $result = $this->db->query($sql, 'i', [$idNumber]);
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Remove sanction
     */
    public function removeSanction($idNumber): bool {
        $sql = "UPDATE {$this->table} 
                SET sanctioned = 0, sanctioned_at = NULL 
                WHERE id_number = ?";
        
        $result = $this->db->query($sql, 'i', [$idNumber]);
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Get users with active loans
     */
    public function getUsersWithActiveLoans(): array {
        $sql = "SELECT u.*, COUNT(p.loan_id) as active_loans
                FROM {$this->table} u
                INNER JOIN loans p ON u.id_number = p.user_id
                WHERE p.returned = 0
                GROUP BY u.id_number";
        
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Create new user with validation
     */
    public function createUser(array $data) {
        // Ensure defaults
        if (!isset($data['sanctioned'])) {
            $data['sanctioned'] = 0;
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($data);
    }
    
    /**
     * Update user with validation
     */
    public function updateUser($idNumber, array $data): bool {
        // Never allow changing the primary key via payload
        unset($data['id_number']);
        return $this->update($idNumber, $data);
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics(): array {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->queryOne($sql);
        $stats['total'] = $result ? $result['total'] : 0;
        
        // Sanctioned users
        $sql = "SELECT COUNT(*) as sanctioned FROM {$this->table} WHERE sanctioned = 1";
        $result = $this->db->queryOne($sql);
        $stats['sanctioned'] = $result ? $result['sanctioned'] : 0;
        
        // Users registered this month
        $sql = "SELECT COUNT(*) as this_month FROM {$this->table} 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->queryOne($sql);
        $stats['this_month'] = $result ? $result['this_month'] : 0;
        
        // Users with active loans
        $sql = "SELECT COUNT(DISTINCT user_id) as with_loans 
                FROM loans WHERE returned = 0";
        $result = $this->db->queryOne($sql);
        $stats['with_active_loans'] = $result ? $result['with_loans'] : 0;
        
        return $stats;
    }
    
    /**
     * Validate user data
     */
    public function validate(array $data, bool $isUpdate = false): array {
        $validator = new Validator();
        
        $rules = [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|max:255',
            'address' => 'max:100',
        ];
        
        if (!$isUpdate) {
            $rules['id_number'] = 'required|numeric|min:1';
            $rules['user_key'] = 'required|numeric|min:1';
            $rules['phone'] = 'numeric';
        }
        
        $validator->validate($data, $rules);
        
        return $validator->getErrors();
    }
}
