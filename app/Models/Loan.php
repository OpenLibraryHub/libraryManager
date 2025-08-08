<?php
/**
 * Loan Model
 * 
 * Handles book loans and returns
 */

namespace App\Models;

use App\Helpers\Validator;
use Exception;

class Loan extends Model {
    protected string $table = 'loans';
    protected string $primaryKey = 'loan_id';
    
    protected array $fillable = [
        'book_id',
        'user_id',
        'note',
        'loaned_at',
        'due_at',
        'returned',
        'returned_at'
    ];
    
    /**
     * Get active loans with book and user details
     */
    public function getActiveLoans(): array {
        $sql = "SELECT 
                p.loan_id AS PrestamosID,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                p.returned AS devuelto,
                p.returned_at AS fecha_entregado,
                l.title AS Titulo,
                l.author AS Autor,
                l.isbn AS ISBN,
                u.first_name AS Nombre,
                u.last_name AS Apellido,
                u.email AS Correo,
                u.id_number AS Cedula
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                INNER JOIN users u ON p.user_id = u.id_number
                WHERE p.returned = 0
                ORDER BY p.loaned_at ASC";
        
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get returned loans with book and user details
     */
    public function getReturnedLoans(): array {
        $sql = "SELECT 
                p.loan_id AS PrestamosID,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                p.returned AS devuelto,
                p.returned_at AS fecha_entregado,
                l.title AS Titulo,
                l.author AS Autor,
                l.isbn AS ISBN,
                u.first_name AS Nombre,
                u.last_name AS Apellido,
                u.email AS Correo,
                u.id_number AS Cedula
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                INNER JOIN users u ON p.user_id = u.id_number
                WHERE p.returned = 1
                ORDER BY p.returned_at DESC";
        
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Search loans
     */
    public function searchLoans(string $query, string $field = 'all', bool $activeOnly = true): array {
        $query = '%' . $this->db->escape($query) . '%';
        
        $baseQuery = "SELECT 
                      p.loan_id AS PrestamosID,
                      p.loaned_at AS fecha_prestamo,
                      p.due_at AS fecha_limite,
                      p.returned AS devuelto,
                      p.returned_at AS fecha_entregado,
                      l.title AS Titulo,
                      l.author AS Autor,
                      u.first_name AS Nombre,
                      u.last_name AS Apellido,
                      u.email AS Correo,
                      u.id_number AS Cedula
                      FROM {$this->table} p
                      INNER JOIN books l ON p.book_id = l.id
                      INNER JOIN users u ON p.user_id = u.id_number";
        
        $whereConditions = [];
        
        if ($activeOnly) {
            $whereConditions[] = "p.returned = 0";
        }
        
        switch ($field) {
            case 'libro':
                $whereConditions[] = "l.title LIKE ?";
                break;
            case 'usuario':
                $whereConditions[] = "u.first_name LIKE ?";
                break;
            case 'llave':
                $whereConditions[] = "u.user_key LIKE ?";
                break;
            case 'cedula':
                $whereConditions[] = "u.id_number LIKE ?";
                break;
            default:
                $whereConditions[] = "(l.title LIKE ? OR u.first_name LIKE ? OR u.id_number LIKE ?)";
        }
        
        $sql = $baseQuery;
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        $sql .= " ORDER BY p.loaned_at ASC";
        
        if ($field === 'all') {
            return $this->db->query($sql, 'sss', [$query, $query, $query]) ?: [];
        } else {
            return $this->db->query($sql, 's', [$query]) ?: [];
        }
    }
    
    /**
     * Create new loan
     */
    public function createLoan(int $bookId, int $userId, string $observation = '', int $daysLimit = 15): bool {
        $this->db->beginTransaction();
        
        try {
            // Check if book is available
            $bookModel = new Book();
            if (!$bookModel->isAvailable($bookId)) {
                throw new Exception("El libro no está disponible para préstamo.");
            }
            
            // Check if user is not sanctioned
            $userModel = new User();
            if ($userModel->isSanctioned($userId)) {
                throw new Exception("El usuario está sancionado y no puede realizar préstamos.");
            }
            
            // Decrease book availability
            if (!$bookModel->decreaseAvailability($bookId)) {
                throw new Exception("No se pudo actualizar la disponibilidad del libro.");
            }
            
            // Create loan record
            $sql = "INSERT INTO {$this->table} 
                    (book_id, user_id, note, loaned_at, due_at, returned) 
                    VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 0)";
            
            $result = $this->db->query($sql, 'iisi', [$bookId, $userId, $observation, $daysLimit]);
            
            if ($result === false) {
                throw new Exception("No se pudo crear el registro de préstamo.");
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Return a loan
     */
    public function returnLoan(int $loanId): bool {
        $this->db->beginTransaction();
        
        try {
            // Get loan details
            $loan = $this->find($loanId);
            if (!$loan) {
                throw new Exception("Préstamo no encontrado.");
            }
            
            if ((int)($loan['returned'] ?? 0) === 1) {
                throw new Exception("Este préstamo ya fue devuelto.");
            }
            
            // Update loan as returned
            $sql = "UPDATE {$this->table} 
                    SET returned = 1, returned_at = NOW() 
                    WHERE loan_id = ?";
            
            $result = $this->db->query($sql, 'i', [$loanId]);
            
            if ($result === false || $this->db->affectedRows() === 0) {
                throw new Exception("No se pudo actualizar el estado del préstamo.");
            }
            
            // Increase book availability
            $bookModel = new Book();
            if (!$bookModel->increaseAvailability((int)$loan['book_id'], 1)) {
                throw new Exception("No se pudo actualizar la disponibilidad del libro.");
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Extend loan deadline
     */
    public function extendLoan(int $loanId, int $additionalDays = 5): bool {
        // Check if loan exists and is active
        $loan = $this->find($loanId);
        if (!$loan || (int)($loan['returned'] ?? 0) === 1) {
            return false;
        }
        
        // Check if deadline hasn't passed
        if (strtotime($loan['due_at']) < time()) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET due_at = DATE_ADD(due_at, INTERVAL ? DAY) 
                WHERE loan_id = ? AND returned = 0 AND NOW() < due_at";
        
        $result = $this->db->query($sql, 'ii', [$additionalDays, $loanId]);
        
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Get overdue loans
     */
    public function getOverdueLoans(): array {
        $sql = "SELECT 
                p.loan_id AS PrestamosID,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                p.returned AS devuelto,
                p.returned_at AS fecha_entregado,
                l.title AS Titulo,
                l.author AS Autor,
                u.first_name AS Nombre,
                u.last_name AS Apellido,
                u.email AS Correo,
                u.id_number AS Cedula,
                DATEDIFF(NOW(), p.due_at) as days_overdue
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                INNER JOIN users u ON p.user_id = u.id_number
                WHERE p.returned = 0 AND p.due_at < NOW()
                ORDER BY p.due_at ASC";
        
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get user's active loans
     */
    public function getUserActiveLoans(int $userId): array {
        $sql = "SELECT 
                p.loan_id AS PrestamosID,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                l.title AS Titulo,
                l.author AS Autor,
                l.isbn AS ISBN
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                WHERE p.user_id = ? AND p.returned = 0
                ORDER BY p.loaned_at DESC";
        
        return $this->db->query($sql, 'i', [$userId]) ?: [];
    }
    
    /**
     * Get user's loan history
     */
    public function getUserLoanHistory(int $userId): array {
        $sql = "SELECT 
                p.loan_id AS PrestamosID,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                p.returned AS devuelto,
                p.returned_at AS fecha_entregado,
                l.title AS Titulo,
                l.author AS Autor,
                l.isbn AS ISBN
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                WHERE p.user_id = ?
                ORDER BY p.loaned_at DESC";
        
        return $this->db->query($sql, 'i', [$userId]) ?: [];
    }
    
    /**
     * Check if user has active loan for a book
     */
    public function userHasActiveLoan(int $userId, int $bookId): bool {
        $sql = "SELECT 1 FROM {$this->table} 
                WHERE user_id = ? AND book_id = ? AND returned = 0 
                LIMIT 1";
        
        $result = $this->db->queryOne($sql, 'ii', [$userId, $bookId]);
        return $result !== null;
    }
    
    /**
     * Get loan statistics
     */
    public function getStatistics(): array {
        $stats = [];
        
        // Total loans
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->queryOne($sql);
        $stats['total'] = $result ? $result['total'] : 0;
        
        // Active loans
        $sql = "SELECT COUNT(*) as active FROM {$this->table} WHERE returned = 0";
        $result = $this->db->queryOne($sql);
        $stats['active'] = $result ? $result['active'] : 0;
        
        // Returned loans
        $stats['returned'] = $stats['total'] - $stats['active'];
        
        // Overdue loans
        $sql = "SELECT COUNT(*) as overdue FROM {$this->table} 
                WHERE returned = 0 AND due_at < NOW()";
        $result = $this->db->queryOne($sql);
        $stats['overdue'] = $result ? $result['overdue'] : 0;
        
        // Loans this month
        $sql = "SELECT COUNT(*) as this_month FROM {$this->table} 
                WHERE MONTH(loaned_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(loaned_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->queryOne($sql);
        $stats['this_month'] = $result ? $result['this_month'] : 0;
        
        // Returns this month
        $sql = "SELECT COUNT(*) as returns_this_month FROM {$this->table} 
                WHERE returned = 1 
                AND MONTH(returned_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(returned_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->queryOne($sql);
        $stats['returns_this_month'] = $result ? $result['returns_this_month'] : 0;
        
        // Most borrowed books
        $sql = "SELECT l.title AS Titulo, l.author AS Autor, COUNT(p.loan_id) as loan_count
                FROM {$this->table} p
                INNER JOIN books l ON p.book_id = l.id
                GROUP BY p.book_id
                ORDER BY loan_count DESC
                LIMIT 10";
        $stats['most_borrowed'] = $this->db->query($sql) ?: [];
        
        // Most active users
        $sql = "SELECT u.first_name AS Nombre, u.last_name AS Apellido, COUNT(p.loan_id) as loan_count
                FROM {$this->table} p
                INNER JOIN users u ON p.user_id = u.id_number
                GROUP BY p.user_id
                ORDER BY loan_count DESC
                LIMIT 10";
        $stats['most_active_users'] = $this->db->query($sql) ?: [];
        
        return $stats;
    }
    
    /**
     * Export loans data for Excel
     */
    public function exportLoansData(): array {
        $sql = "SELECT 
                l.title AS libro,
                l.author AS autor,
                l.classification_id AS clasificacion,
                l.classification_code AS clasificacion_completa,
                CONCAT(u.first_name, ' ', u.last_name) AS nombre,
                u.id_number AS cedula,
                p.note AS observacion,
                p.loaned_at AS fecha_prestamo,
                p.due_at AS fecha_limite,
                IF(p.returned = 1, 'SI', 'NO') AS devuelto,
                p.returned_at AS fecha_entregado
                FROM {$this->table} p
                INNER JOIN books l ON l.id = p.book_id
                INNER JOIN users u ON u.id_number = p.user_id
                ORDER BY p.loaned_at DESC";
        
        return $this->db->query($sql) ?: [];
    }
}
