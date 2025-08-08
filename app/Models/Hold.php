<?php
namespace App\Models;

class Hold extends Model {
    protected string $table = 'holds';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'book_id', 'user_id', 'status', 'created_at', 'fulfilled_at', 'canceled_at'
    ];

    public function createHold(int $bookId, int $userId): bool {
        $data = [
            'book_id' => $bookId,
            'user_id' => $userId,
            'status' => 'queued',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return (bool)$this->create($data);
    }

    public function getQueueForBook(int $bookId): array {
        $sql = "SELECT h.*, u.first_name, u.last_name, u.id_number
                FROM {$this->table} h
                INNER JOIN users u ON u.id_number = h.user_id
                WHERE h.book_id = ? AND h.status = 'queued'
                ORDER BY h.created_at ASC";
        return $this->db->query($sql, 'i', [$bookId]) ?: [];
    }

    public function nextInQueue(int $bookId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE book_id = ? AND status = 'queued'
                ORDER BY created_at ASC LIMIT 1";
        return $this->db->queryOne($sql, 'i', [$bookId]);
    }

    public function markFulfilled(int $holdId): bool {
        $sql = "UPDATE {$this->table} SET status = 'fulfilled', fulfilled_at = NOW() WHERE id = ?";
        $res = $this->db->query($sql, 'i', [$holdId]);
        return $res !== false && $this->db->affectedRows() > 0;
    }

    public function cancelHold(int $holdId): bool {
        $sql = "UPDATE {$this->table} SET status = 'canceled', canceled_at = NOW() WHERE id = ?";
        $res = $this->db->query($sql, 'i', [$holdId]);
        return $res !== false && $this->db->affectedRows() > 0;
    }

    public function userHasHold(int $bookId, int $userId): bool {
        $sql = "SELECT 1 FROM {$this->table} WHERE book_id = ? AND user_id = ? AND status = 'queued' LIMIT 1";
        $row = $this->db->queryOne($sql, 'ii', [$bookId, $userId]);
        return $row !== null;
    }
}


