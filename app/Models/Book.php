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
                c.description as classification,
                o.donated_by as origin,
                e.description as label,
                s.description as room
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
                c.description as classification_desc,
                o.donated_by as origin_desc,
                e.description as label_desc,
                e.color as label_color,
                s.description as room_desc
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
                c.description as classification_desc,
                s.description as room
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
                c.description as classification,
                o.donated_by,
                e.description as label,
                s.description as room
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
                      c.description as classification,
                      o.donated_by,
                      e.description as label,
                      s.description as room
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
            case 'title':
                $whereConditions[] = "l.title LIKE ?";
                break;
            case 'author':
                $whereConditions[] = "l.author LIKE ?";
                break;
            case 'isbn':
                $whereConditions[] = "l.isbn LIKE ?";
                break;
            case 'code':
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
        return $result !== false;
    }
    
    /**
     * Update book availability (decrease)
     */
    public function decreaseAvailability($id, int $amount = 1): bool {
        $sql = "UPDATE {$this->table} 
                SET copies_available = CASE WHEN copies_available >= ? THEN copies_available - ? ELSE copies_available END
                WHERE id = ?";
        
        $result = $this->db->query($sql, 'iii', [$amount, $amount, $id]);
        return $result !== false;
    }
    
    /**
     * Check if book is available for loan
     */
    public function isAvailable($id): bool {
        $sql = "SELECT copies_available, notes FROM {$this->table} WHERE id = ? LIMIT 1";
        $result = $this->db->queryOne($sql, 'i', [$id]);
        if (!$result) { return false; }
        // Consider archived (notes starts with [ARCHIVED]) as not available
        $notes = (string)($result['notes'] ?? '');
        if (strncmp($notes, '[ARCHIVED]', 10) === 0) { return false; }
        return (int)$result['copies_available'] > 0;
    }

    /**
     * Books eligible for waitlist: unavailable (0 copies) and not archived/deleted (copies_total > 0 and notes not prefixed)
     */
    public function getWaitlistEligibleBooks(): array {
        $sql = "SELECT * FROM {$this->table}
                WHERE copies_total > 0
                  AND copies_available = 0
                  AND (notes IS NULL OR notes NOT LIKE '[ARCHIVED]%')
                ORDER BY title ASC";
        return $this->db->query($sql) ?: [];
    }

    /**
     * Update core book fields (admin)
     */
    public function updateBook(int $id, array $data): bool {
        $payload = array_intersect_key($data, array_flip([
            'isbn', 'title', 'author', 'classification_id', 'classification_code',
            'origin_id', 'label_id', 'room_id', 'notes'
        ]));
        if (empty($payload)) { return false; }
        return $this->update($id, $payload);
    }

    /**
     * Logical archive: set copies to zero and mark notes
     */
    public function archiveLogical(int $id): bool {
        $sql = "UPDATE {$this->table} 
                SET copies_available = 0, copies_total = 0, notes = CONCAT('[ARCHIVED] ', COALESCE(notes, ''))
                WHERE id = ?";
        $res = $this->db->query($sql, 'i', [$id]);
        return $res !== false && $this->db->affectedRows() > 0;
    }

    /**
     * Heuristic to detect archived books without DB schema change
     */
    public static function isArchivedRow(array $row): bool {
        $copiesTotal = (int)($row['copies_total'] ?? 0);
        $copiesAvail = (int)($row['copies_available'] ?? 0);
        $notes = (string)($row['notes'] ?? '');
        return $copiesTotal === 0 && $copiesAvail === 0 && strncmp($notes, '[ARCHIVED]', 10) === 0;
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
        // Accept English keys directly
        // Coerce numeric FKs safely (null if non-numeric or <= 0)
        $toNullablePositiveInt = function($value) {
            if ($value === null || $value === '') { return null; }
            if (!is_numeric($value)) { return null; }
            $iv = (int)$value;
            return $iv > 0 ? $iv : null;
        };

        $payload = [
            'isbn' => $data['isbn'] ?? null,
            'title' => $data['title'] ?? null,
            'author' => $data['author'] ?? null,
            'classification_id' => $toNullablePositiveInt($data['classification_id'] ?? null),
            'classification_code' => $data['classification_code'] ?? null,
            'copies_total' => isset($data['copies_total']) ? (int)$data['copies_total'] : null,
            'origin_id' => $toNullablePositiveInt($data['origin_id'] ?? null),
            'copies_available' => isset($data['copies_available']) ? (int)$data['copies_available'] : (isset($data['copies_total']) ? (int)$data['copies_total'] : 0),
            'label_id' => $toNullablePositiveInt($data['label_id'] ?? null),
            'library_id' => $data['library_id'] ?? 683070001001,
            'room_id' => $toNullablePositiveInt($data['room_id'] ?? null),
            'notes' => $data['notes'] ?? null,
        ];

        // Verify FK existence; set to NULL if not found to avoid FK violation
        if ($payload['classification_id'] !== null) {
            $exists = $this->db->queryOne('SELECT 1 FROM classifications WHERE classification_id = ? LIMIT 1', 'i', [$payload['classification_id']]);
            if ($exists === null) { $payload['classification_id'] = null; }
        }
        if ($payload['origin_id'] !== null) {
            $exists = $this->db->queryOne('SELECT 1 FROM origins WHERE origin_id = ? LIMIT 1', 'i', [$payload['origin_id']]);
            if ($exists === null) { $payload['origin_id'] = null; }
        }
        if ($payload['label_id'] !== null) {
            $exists = $this->db->queryOne('SELECT 1 FROM labels WHERE label_id = ? LIMIT 1', 'i', [$payload['label_id']]);
            if ($exists === null) { $payload['label_id'] = null; }
        }
        if ($payload['room_id'] !== null) {
            $exists = $this->db->queryOne('SELECT 1 FROM rooms WHERE room_id = ? LIMIT 1', 'i', [$payload['room_id']]);
            if ($exists === null) { $payload['room_id'] = null; }
        }
        return $this->create($payload);
    }
    
    /**
     * Validate book data
     */
    public function validate(array $data, bool $isUpdate = false): array {
        $validator = new Validator();
        
        $rules = [
            'title' => 'required|min:1|max:300',
            'author' => 'max:512',
            'classification_id' => 'max:100',
            'copies_total' => 'integer|min:1',
            'copies_available' => 'integer|min:0',
            'origin_id' => 'integer',
            'room_id' => 'integer',
        ];
        
        // ISBN is optional; allow up to 32 chars (strict checksum not required)
        if (isset($data['isbn']) && $data['isbn'] !== null && $data['isbn'] !== '') {
            $rules['isbn'] = 'max:32';
        }
        
        if (isset($data['classification_code']) && !empty($data['classification_code'])) {
            $rules['classification_code'] = 'max:100';
        }
        
        if (isset($data['notes']) && !empty($data['notes'])) {
            $rules['notes'] = 'max:100';
        }
        
        $validator->validate($data, $rules);
        $errors = $validator->getErrors();
        
        // Additional validation: copies_available should not exceed copies_total
        if (isset($data['copies_available'], $data['copies_total']) && $data['copies_available'] > $data['copies_total']) {
            $errors['copies_available'][] = 'Available copies cannot be greater than total copies.';
        }
        
        // Uniqueness check for ISBN when provided; skip if blank
        if (isset($data['isbn']) && $data['isbn'] !== null && $data['isbn'] !== '') {
            $excludeId = $isUpdate ? ($data['id'] ?? null) : null;
            if ($this->isbnExists($data['isbn'], $excludeId)) {
                $errors['isbn'][] = 'Este ISBN ya existe.';
            }
        }
        
        return $errors;
    }
}
