<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Models\User;

register_test('User: create and search', function() {
    $userModel = new User();

    $id = random_int(100000, 999999);
    $key = random_int(1000, 9999);
    $email = 'reader+' . uniqid() . '@example.com';

    $errors = $userModel->validate([
        'id_number' => $id,
        'user_key' => $key,
        'first_name' => 'Alice',
        'last_name' => 'Wonder',
        'email' => $email,
    ]);
    assertTrue(empty($errors), 'Validation failed: ' . json_encode($errors));

    $created = $userModel->createUser([
        'id_number' => $id,
        'user_key' => $key,
        'first_name' => 'Alice',
        'last_name' => 'Wonder',
        'email' => $email,
    ]);
    assertNotNull($created, 'Failed to create user');

    $found = $userModel->findByIdentifier($id);
    assertEquals($id, (int)($found['id_number'] ?? 0), 'User not found by id');

    $results = $userModel->search('Alice', 'first_name');
    assertTrue(is_array($results) && count($results) >= 1, 'Search by first_name failed');
});
