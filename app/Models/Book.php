<?php
/**
 * Book Model
 * 
 * Handles book data and operations
 */

namespace App\Models;

use App\Helpers\Validator;

class Book extends Model {
    protected string $table = 'books';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'isbn',
        'title',
        'author',
        'classification_id',
        'classification_code',
        'copies_total',
        'origin_id',
        'copies_available',
        'label_id',
        'library_id',
        'room_id',
        'notes'
    ];
    
    /** Count all books (optionally only available) */
    public function countAll(bool $onlyAvailable = false): int {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";
        if ($onlyAvailable) {
            $sql .= " WHERE copies_available >= 1";
        }
        $row = $this->db->queryOne($sql);
        return $row ? (int)$row['total'] : 0;
    }

    /** Get a page of books with joins */
    public function getPage(int $limit, int $offset, bool $onlyAvailable = false): array {
        $sql = "SELECT l.*, 
                c.description as clasificacion,
                o.donated_by as origen,
                e.description as etiqueta,
                s.description as sala
                FROM {$this->table} l
                LEFT JOIN classifications c ON l.classification_id = c.classification_id
                LEFT JOIN origins o ON l.origin_id = o.origin_id
                LEFT JOIN labels e ON l.label_id = e.label_id
                LEFT JOIN rooms s ON l.room_id = s.room_id";
        $types = '';
        $params = [];
        if ($onlyAvailable) {
            $sql .= " WHERE l.copies_available >= 1";
        }
        $sql .= " ORDER BY l.title ASC LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->query($sql, $types, $params) ?: [];
    }
    
    /**
     * Get book with related information
     */
    public function getBookWithDetails($id): ?array {
        $sql = "SELECT l.*, 
                c.description as clasificacion_desc,
                o.donated_by as origen_desc,
                e.description as etiqueta_desc,
                e.color as etiqueta_color,
                s.description as sala_desc
                FROM {$this->table} l
                LEFT JOIN classifications c ON l.classification_id = c.classification_id
                LEFT JOIN origins o ON l.origin_id = o.origin_id
                LEFT JOIN labels e ON l.label_id = e.label_id
                LEFT JOIN rooms s ON l.room_id = s.room_id
                WHERE l.id = ?
                LIMIT 1";
        
        return $this->db->queryOne($sql, 'i', [$id]);
    }
    
    /**
     * Find book by ISBN
     */
    public function findByISBN($isbn): ?array {
        $sql = "SELECT l.*, 
                c.description as ClasificacionDesc,
                s.description as sala
                FROM {$this->table} l
                LEFT JOIN classifications c ON l.classification_id = c.classification_id
                LEFT JOIN rooms s ON l.room_id = s.room_id
                WHERE l.isbn = ?
                LIMIT 1";
        
        return $this->db->queryOne($sql, 's', [$isbn]);
    }
    
    /**
     * Get available books
     */
    public function getAvailableBooks(): array {
        $sql = "SELECT l.*, 
                c.description as clasificacion,
                o.donated_by,
                e.description as etiqueta,
                s.description
                FROM {$this->table} l
                LEFT JOIN classifications c ON l.classification_id = c.classification_id
                LEFT JOIN origins o ON l.origin_id = o.origin_id
                LEFT JOIN labels e ON l.label_id = e.label_id
                LEFT JOIN rooms s ON l.room_id = s.room_id
                WHERE l.copies_available >= 1
                ORDER BY l.title ASC";
        
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Search books
     */
    public function searchBooks(string $query, string $field = 'all', bool $onlyAvailable = true): array {
        $query = '%' . $this->db->escape($query) . '%';
        
        $baseQuery = "SELECT l.*, 
                      c.description as clasificacion,
                      o.donated_by,
                      e.description as etiqueta,
                      s.description
                      FROM {$this->table} l
                      LEFT JOIN classifications c ON l.classification_id = c.classification_id
                      LEFT JOIN origins o ON l.origin_id = o.origin_id
                      LEFT JOIN labels e ON l.label_id = e.label_id
                      LEFT JOIN rooms s ON l.room_id = s.room_id";
        
        $whereConditions = [];
        
        if ($onlyAvailable) {
            $whereConditions[] = "l.copies_available >= 1";
        }
        
        switch ($field) {
            case 'titulo':
                $whereConditions[] = "l.title LIKE ?";
                break;
            case 'autor':
                $whereConditions[] = "l.author LIKE ?";
                break;
            case 'isbn':
                $whereConditions[] = "l.isbn LIKE ?";
                break;
            case 'codigo':
                $whereConditions[] = "l.classification_code LIKE ?";
                break;
            default:
                $whereConditions[] = "(l.title LIKE ? OR l.author LIKE ? OR l.isbn LIKE ? OR l.classification_code LIKE ?)";
        }
        
        $sql = $baseQuery;
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        $sql .= " ORDER BY l.title ASC";
        
        if ($field === 'all') {
            return $this->db->query($sql, 'ssss', [$query, $query, $query, $query]) ?: [];
        } else {
            return $this->db->query($sql, 's', [$query]) ?: [];
        }
    }
    
    /**
     * Update book availability (increase)
     */
    public function increaseAvailability($id, int $amount = 1): bool {
        $sql = "UPDATE {$this->table} 
                SET copies_available = copies_available + ?, 
                    copies_total = copies_total + ?
                WHERE id = ?";
        
        $result = $this->db->query($sql, 'iii', [$amount, $amount, $id]);
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Update book availability (decrease)
     */
    public function decreaseAvailability($id, int $amount = 1): bool {
        $sql = "UPDATE {$this->table} 
                SET copies_available = copies_available - ?
                WHERE id = ? AND copies_available >= ?";
        
        $result = $this->db->query($sql, 'iii', [$amount, $id, $amount]);
        return $result !== false && $this->db->affectedRows() > 0;
    }
    
    /**
     * Check if book is available for loan
     */
    public function isAvailable($id): bool {
        $sql = "SELECT copies_available FROM {$this->table} WHERE id = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$id]);
        
        return $result && $result['copies_available'] > 0;
    }
    
    /**
     * Get book statistics
     */
    public function getStatistics(): array {
        $stats = [];
        
        // Total books
        $sql = "SELECT COUNT(*) as total, 
                SUM(copies_total) as total_copies,
                SUM(copies_available) as total_available
                FROM {$this->table}";
        $result = $this->db->queryOne($sql);
        
        $stats['total_titles'] = $result ? $result['total'] : 0;
        $stats['total_copies'] = $result ? $result['total_copies'] : 0;
        $stats['total_available'] = $result ? $result['total_available'] : 0;
        
        // Books on loan
        $stats['total_on_loan'] = $stats['total_copies'] - $stats['total_available'];
        
        // Books by classification
        $sql = "SELECT c.description, COUNT(l.id) as count
                FROM {$this->table} l
                LEFT JOIN classifications c ON l.classification_id = c.classification_id
                GROUP BY l.classification_id
                ORDER BY count DESC
                LIMIT 10";
        $stats['by_classification'] = $this->db->query($sql) ?: [];
        
        // Books by origin
        $sql = "SELECT o.donated_by, COUNT(l.id) as count
                FROM {$this->table} l
                LEFT JOIN origins o ON l.origin_id = o.origin_id
                GROUP BY l.origin_id";
        $stats['by_origin'] = $this->db->query($sql) ?: [];
        
        return $stats;
    }
    
    /**
     * Get all classifications
     */
    public function getClassifications(): array {
        $sql = "SELECT classification_id as id, description as body 
                FROM classifications 
                ORDER BY classification_id";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get all origins
     */
    public function getOrigins(): array {
        $sql = "SELECT origin_id as id, donated_by as body 
                FROM origins 
                ORDER BY origin_id";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get all labels
     */
    public function getLabels(): array {
        $sql = "SELECT label_id as id, 
                CONCAT(color, ' - ', description) as body 
                FROM labels 
                ORDER BY label_id";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Get all rooms
     */
    public function getRooms(): array {
        $sql = "SELECT room_id as id, description as body 
                FROM rooms 
                ORDER BY room_id";
        return $this->db->query($sql) ?: [];
    }
    
    /**
     * Check if an ISBN (LibrosID) already exists
     */
    public function isbnExists($isbn, $excludeId = null): bool {
        if ($excludeId !== null) {
            $sql = "SELECT 1 FROM {$this->table} WHERE isbn = ? AND id <> ? LIMIT 1";
            $result = $this->db->queryOne($sql, 'si', [$isbn, $excludeId]);
        } else {
            $sql = "SELECT 1 FROM {$this->table} WHERE isbn = ? LIMIT 1";
            $result = $this->db->queryOne($sql, 's', [$isbn]);
        }
        return $result !== null;
    }
    
    /**
     * Create book with validation
     */
    public function createBook(array $data) {
        // Map Spanish form keys to English DB columns
        $payload = [
            'isbn' => $data['LibrosID'] ?? null,
            'title' => $data['Titulo'] ?? null,
            'author' => $data['Autor'] ?? null,
            'classification_id' => $data['ClasificacionID'] ?? null,
            'classification_code' => $data['CodigoClasificacion'] ?? null,
            'copies_total' => isset($data['N_Ejemplares']) ? (int)$data['N_Ejemplares'] : null,
            'origin_id' => isset($data['OrigenID']) ? (int)$data['OrigenID'] : null,
            'copies_available' => isset($data['N_Disponible']) ? (int)$data['N_Disponible'] : (isset($data['N_Ejemplares']) ? (int)$data['N_Ejemplares'] : 0),
            'label_id' => ($data['EtiquetaID'] ?? '') === '' ? null : $data['EtiquetaID'],
            'library_id' => $data['BibliotecaID'] ?? 683070001001,
            'room_id' => isset($data['SalaID']) ? (int)$data['SalaID'] : null,
            'notes' => $data['Observacion'] ?? null,
        ];
        return $this->create($payload);
    }
    
    /**
     * Validate book data
     */
    public function validate(array $data, bool $isUpdate = false): array {
        $validator = new Validator();
        
        $rules = [
            'Titulo' => 'required|min:1|max:300',
            'Autor' => 'max:512',
            'ClasificacionID' => 'max:100',
            'N_Ejemplares' => 'integer|min:1',
            'N_Disponible' => 'integer|min:0',
            'OrigenID' => 'integer',
            'SalaID' => 'integer',
        ];
        
        // Only validate ISBN format if provided (non-empty)
        if (isset($data['LibrosID']) && $data['LibrosID'] !== null && $data['LibrosID'] !== '') {
            $rules['LibrosID'] = 'numeric';
        }
        
        if (isset($data['CodigoClasificacion']) && !empty($data['CodigoClasificacion'])) {
            $rules['CodigoClasificacion'] = 'max:100';
        }
        
        if (isset($data['Observacion']) && !empty($data['Observacion'])) {
            $rules['Observacion'] = 'max:100';
        }
        
        $validator->validate($data, $rules);
        $errors = $validator->getErrors();
        
        // Additional validation: N_Disponible should not exceed N_Ejemplares
        if (isset($data['N_Disponible'], $data['N_Ejemplares']) && $data['N_Disponible'] > $data['N_Ejemplares']) {
            $errors['N_Disponible'][] = 'El número disponible no puede ser mayor que el número de ejemplares.';
        }
        
        // Uniqueness check for ISBN when provided; skip if blank
        if (!$isUpdate && isset($data['LibrosID']) && $data['LibrosID'] !== null && $data['LibrosID'] !== '') {
            if ($this->isbnExists($data['LibrosID'])) {
                $errors['LibrosID'][] = 'Este ISBN ya existe.';
            }
        }
        
        return $errors;
    }
}
