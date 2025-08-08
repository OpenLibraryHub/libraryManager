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
     * Find user by cedula or llave
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
     * Search users
     */
    public function search(string $query, string $field = 'all'): array {
        $query = '%' . $this->db->escape($query) . '%';
        
        switch ($field) {
            case 'cedula':
                $sql = "SELECT * FROM {$this->table} WHERE id_number LIKE ?";
                break;
            case 'nombre':
                $sql = "SELECT * FROM {$this->table} WHERE first_name LIKE ?";
                break;
            case 'apellido':
                $sql = "SELECT * FROM {$this->table} WHERE last_name LIKE ?";
                break;
            case 'correo':
                $sql = "SELECT * FROM {$this->table} WHERE email LIKE ?";
                break;
            case 'llave':
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
     * Check if user exists by email, cedula, or llave
     */
    public function userExists($email, $cedula, $llave, $phone = null): bool {
        $sql = "SELECT 1 FROM {$this->table} 
                WHERE email = ? OR id_number = ? OR user_key = ?";
        
        $params = [$email, $cedula, $llave];
        $types = 'sii';
        
        if ($phone !== null) {
            $sql .= " OR phone = ?";
            $params[] = $phone;
            $types .= 'i';
        }
        
        $sql .= " LIMIT 1";
        
        $result = $this->db->queryOne($sql, $types, $params);
        return $result !== null;
    }
    
    /**
     * Check if user is sanctioned
     */
    public function isSanctioned($cedula): bool {
        $sql = "SELECT sanctioned FROM {$this->table} WHERE id_number = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$cedula]);
        return $result && (int)$result['sanctioned'] === 1;
    }
    
    /**
     * Sanction user
     */
    public function sanction($cedula): bool {
        $sql = "UPDATE {$this->table} 
                SET sanctioned = 1, sanctioned_at = NOW() 
                WHERE id_number = ?";
        
        $result = $this->db->query($sql, 'i', [$cedula]);
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Remove sanction
     */
    public function removeSanction($cedula): bool {
        $sql = "UPDATE {$this->table} 
                SET sanctioned = 0, sanctioned_at = NULL 
                WHERE id_number = ?";
        
        $result = $this->db->query($sql, 'i', [$cedula]);
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
        // Set current datetime for Fecha field
        $data['Fecha'] = date('Y-m-d H:i:s');
        
        // Ensure sancionado is set to 0 by default
        if (!isset($data['sancionado'])) {
            $data['sancionado'] = 0;
        }
        
        return $this->create($data);
    }
    
    /**
     * Update user with validation
     */
    public function updateUser($cedula, array $data): bool {
        // Remove cedula from data if present (it's the primary key)
        unset($data['Cedula']);
        
        // Use the cedula as ID for update
        return $this->update($cedula, $data);
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
            'Nombre' => 'required|min:2|max:100',
            'Apellido' => 'required|min:2|max:100',
            'Correo' => 'required|email|max:255',
            'direccion' => 'max:100',
        ];
        
        if (!$isUpdate) {
            $rules['Cedula'] = 'required|numeric|min:1';
            $rules['UsuariosID'] = 'required|numeric|min:1';
            $rules['numero'] = 'numeric';
        }
        
        $validator->validate($data, $rules);
        
        return $validator->getErrors();
    }
}
