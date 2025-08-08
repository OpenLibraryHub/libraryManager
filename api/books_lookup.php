<?php
require_once dirname(__DIR__) . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\Book;

header('Content-Type: application/json; charset=utf-8');

if (!AuthMiddleware::check(false)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
$limit = max(1, min(20, (int)($_GET['limit'] ?? 10)));

$results = [];
if ($q !== '') {
    $bookModel = new Book();
    $rows = $bookModel->searchBooks($q, 'all', false);
    foreach ($rows as $row) {
        $results[] = [
            'id' => (int)($row['id'] ?? 0),
            'title' => $row['title'] ?? '',
            'author' => $row['author'] ?? '',
            'isbn' => $row['isbn'] ?? '',
        ];
        if (count($results) >= $limit) break;
    }
}

echo json_encode(['data' => $results]);
exit;


