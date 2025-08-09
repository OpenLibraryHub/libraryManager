<?php
// Test bootstrap: load app, set test env, ensure DB exists
require_once dirname(__DIR__) . '/config/autoload.php';

// Force debug off for predictable output
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

// Basic assertion helper
function assertTrue($cond, $message = 'Expected condition to be true') {
    if (!$cond) throw new Exception($message);
}
function assertEquals($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        $msg = $message !== '' ? $message : 'Expected ' . var_export($expected, true) . ' === ' . var_export($actual, true);
        throw new Exception($msg);
    }
}
function assertNotNull($value, $message = 'Expected non-null') {
    if ($value === null) throw new Exception($message);
}

// DB helper for tests
use Config\Database;

function test_db() : mysqli { // return raw mysqli for convenience
    $db = Database::getInstance();
    return $db->getConnection();
}

function db_begin() { test_db()->begin_transaction(); }
function db_rollback() { test_db()->rollback(); }

// Ensure schema exists minimally (idempotent)
(function () {
    $conn = test_db();
    // Quick check: does librarians table exist?
    $res = $conn->query("SHOW TABLES LIKE 'librarians'");
    if ($res && $res->num_rows > 0) return;
    $schemaPath = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schemaPath)) return; // best-effort
    $sql = file_get_contents($schemaPath);
    // Split on semicolons while preserving engine directives; naive but fine for our schema
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt === '' || strpos($stmt, '--') === 0) continue;
        @$conn->query($stmt);
    }
})();
