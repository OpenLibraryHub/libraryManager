<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\AuthController;
use App\Models\Librarian;
use App\Helpers\Session;

register_test('Auth: login flow', function() {
    // Create a librarian
    $lib = new Librarian();
    $email = 'auth+' . uniqid() . '@example.com';
    $password = 'Sup3rSecret!';
    $created = $lib->createLibrarian([
        'first_name' => 'Auth',
        'paternal_last_name' => 'Tester',
        'email' => $email,
        'password' => $password,
    ]);
    assertNotNull($created, 'Failed to create librarian');

    // Perform login
    $_POST = ['email' => $email, 'password' => $password];
    $_SERVER['REQUEST_METHOD'] = 'POST';

    Session::start();
    $controller = new AuthController();
    $result = $controller->login($_POST);

    assertTrue($result['success'] === true, 'Login should succeed');
    assertTrue(Session::isLoggedIn(), 'Session should indicate logged-in');
});
