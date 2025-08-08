<?php
require_once dirname(__DIR__) . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Models\User;

header('Content-Type: application/json; charset=utf-8');

if (!AuthMiddleware::check(false)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';
$limit = max(1, min(20, (int)($_GET['limit'] ?? 10)));

$results = [];
if ($q !== '') {
    $userModel = new User();
    $rows = $userModel->search($q, $field);
    foreach ($rows as $row) {
        $results[] = [
            'id_number' => (int)($row['id_number'] ?? 0),
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'user_key' => $row['user_key'] ?? '',
            'email' => $row['email'] ?? '',
        ];
        if (count($results) >= $limit) break;
    }
}

echo json_encode(['data' => $results]);
exit;


