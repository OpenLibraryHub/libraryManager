<?php
/**
 * Librarian Model
 * 
 * Handles librarian authentication and management
 */

namespace App\Models;

use App\Helpers\Validator;

class Librarian extends Model {
    protected string $table = 'librarians';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'first_name',
        'email',
        'password',
        'paternal_last_name',
        'maternal_last_name',
        'middle_name'
    ];
    
    protected array $hidden = [
        'password'
    ];
    
    /**
     * Find librarian by email
     */
    public function findByEmail(string $email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        return $this->db->queryOne($sql, 's', [$email]);
    }
    
    /**
     * Authenticate librarian
     * 
     * @param string $email
     * @param string $password
     * @return array|false Returns librarian data if authenticated, false otherwise
     */
    public function authenticate(string $email, string $password) {
        // Find librarian by email
        $librarian = $this->findByEmail($email);
        
        if (!$librarian) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $librarian['password'])) {
            return false;
        }
        
        // Check if password needs rehashing
        if (password_needs_rehash($librarian['password'], PASSWORD_ALGO, PASSWORD_OPTIONS)) {
            $this->updatePassword($librarian['id'], $password);
        }
        
        // Remove password from returned data
        unset($librarian['password']);
        
        return $librarian;
    }
    
    /**
     * Create new librarian
     */
    public function createLibrarian(array $data) {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update librarian
     */
    public function updateLibrarian($id, array $data): bool {
        // Hash password if it's being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        } else {
            // Don't update password if not provided
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, string $newPassword): bool {
        $hashedPassword = $this->hashPassword($newPassword);
        
        $connection = $this->db->getConnection();
        $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        
        $stmt->bind_param('si', $hashedPassword, $id);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $result && $affectedRows > 0;
    }
    
    /**
     * Hash password
     */
    private function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ALGO, PASSWORD_OPTIONS);
    }
    
    /**
     * Verify current password
     */
    public function verifyPassword($id, string $password): bool {
        $sql = "SELECT password FROM {$this->table} WHERE id = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$id]);
        
        if (!$result) {
            return false;
        }
        
        return password_verify($password, $result['password']);
    }
    
    /**
     * Check if email exists (for another librarian)
     */
    public function emailExists(string $email, $excludeId = null): bool {
        if ($excludeId) {
            $sql = "SELECT 1 FROM {$this->table} WHERE email = ? AND id != ? LIMIT 1";
            $result = $this->db->queryOne($sql, 'si', [$email, $excludeId]);
        } else {
            $sql = "SELECT 1 FROM {$this->table} WHERE email = ? LIMIT 1";
            $result = $this->db->queryOne($sql, 's', [$email]);
        }
        
        return (bool)$result;
    }
    
    /**
     * Get librarian statistics
     */
    public function getStatistics(): array {
        $stats = [];
        
        // Total librarians
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->queryOne($sql);
        $stats['total'] = $result ? $result['total'] : 0;
        
        return $stats;
    }
    
    /**
     * Validate librarian data
     */
    public function validate(array $data, bool $isUpdate = false): array {
        $validator = new Validator();
        
        $rules = [
            'first_name' => 'required|min:2|max:50',
            'paternal_last_name' => 'required|min:2|max:50',
            'email' => 'required|email|max:100',
        ];
        
        if (!$isUpdate || !empty($data['password'])) {
            $rules['password'] = 'required|min:8';
        }
        
        if (isset($data['maternal_last_name'])) {
            $rules['maternal_last_name'] = 'max:50';
        }
        
        if (isset($data['middle_name'])) {
            $rules['middle_name'] = 'max:50';
        }
        
        $validator->validate($data, $rules);
        
        return $validator->getErrors();
    }
    
    /**
     * Get full name of librarian
     */
    public function getFullName($librarian): string {
        $name = $librarian['first_name'];
        
        if (!empty($librarian['middle_name'])) {
            $name .= ' ' . $librarian['middle_name'];
        }
        
        $name .= ' ' . $librarian['paternal_last_name'];
        
        if (!empty($librarian['maternal_last_name'])) {
            $name .= ' ' . $librarian['maternal_last_name'];
        }
        
        return $name;
    }
    
    /**
     * Log login attempt
     */
    public function logLoginAttempt(string $email, bool $success, string $ip = null): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'email' => $email,
            'success' => $success,
            'ip' => $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = LOGS_PATH . '/login_attempts.log';
        $logEntry = json_encode($logData) . PHP_EOL;
        error_log($logEntry, 3, $logFile);
    }
    
    /**
     * Store password reset token
     */
    public function storeResetToken($id, string $token, string $expires): bool {
        $connection = $this->db->getConnection();
        $sql = "UPDATE {$this->table} SET reset_token = ?, reset_token_expires = ? WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        
        $stmt->bind_param('ssi', $token, $expires, $id);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $result && $affectedRows > 0;
    }
    
    /**
     * Find librarian by reset token
     */
    public function findByResetToken(string $token) {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1";
        $result = $this->db->queryOne($sql, 's', [$token]);
        
        if ($result) {
            // Remove password from returned data
            unset($result['password']);
        }
        
        return $result;
    }
    
    /**
     * Reset password with token and clear token
     */
    public function resetPasswordWithToken($id, string $newPassword): bool {
        $hashedPassword = $this->hashPassword($newPassword);
        
        $connection = $this->db->getConnection();
        $sql = "UPDATE {$this->table} SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        
        $stmt->bind_param('si', $hashedPassword, $id);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $result && $affectedRows > 0;
    }
}
