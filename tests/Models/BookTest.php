<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Models\Book;

register_test('Book: create, fetch and availability updates', function() {
    $bookModel = new Book();

    $book = $bookModel->createBook([
        'isbn' => '978' . random_int(100000000, 999999999),
        'title' => 'Testing In PHP',
        'author' => 'Quality Bot',
        'copies_total' => 3,
        'copies_available' => 3,
    ]);
    assertNotNull($book, 'Failed to create book');

    $fetched = $bookModel->getBookWithDetails($book['id']);
    assertEquals($book['id'], $fetched['id'] ?? null, 'Book not found');

    assertTrue($bookModel->isAvailable($book['id']), 'Book should be available');

    // Decrease availability
    $ok = $bookModel->decreaseAvailability($book['id'], 2);
    assertTrue($ok, 'Decrease availability failed');

    $still = $bookModel->getBookWithDetails($book['id']);
    assertEquals(1, (int)($still['copies_available'] ?? -1), 'Availability not updated');
});
