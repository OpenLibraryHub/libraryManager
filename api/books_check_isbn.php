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

$isbn = trim((string)($_GET['isbn'] ?? ''));
if ($isbn === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing isbn']);
    exit;
}

$bookModel = new Book();
$row = $bookModel->findByISBN($isbn);

if ($row) {
    echo json_encode([
        'found' => true,
        'book' => [
            'id' => (int)($row['id'] ?? 0),
            'title' => $row['title'] ?? '',
            'author' => $row['author'] ?? '',
            'isbn' => $row['isbn'] ?? ''
        ]
    ]);
} else {
    echo json_encode(['found' => false]);
}
exit;


