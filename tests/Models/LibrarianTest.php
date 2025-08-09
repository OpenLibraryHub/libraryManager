<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Models\Librarian;
use Config\Database;

register_test('Librarian: create and authenticate', function() {
    $model = new Librarian();

    // Unique email per run
    $email = 'tester+' . uniqid() . '@example.com';
    $data = [
        'first_name' => 'Test',
        'paternal_last_name' => 'User',
        'email' => $email,
        'password' => 'StrongPassw0rd!',
    ];

    $created = $model->createLibrarian($data);
    assertNotNull($created, 'Failed to create librarian');
    assertEquals($email, $created['email'] ?? null, 'Email mismatch');

    // Authenticate with raw password
    $auth = $model->authenticate($email, 'StrongPassw0rd!');
    assertTrue(is_array($auth), 'Authentication failed with correct credentials');
    assertTrue(!isset($auth['password']), 'Password must not be exposed');
});
