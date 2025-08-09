<?php
// Simple test runner
require __DIR__ . '/bootstrap.php';

$tests = [];

function register_test(string $name, callable $fn) {
    global $tests; $tests[] = [$name, $fn];
}

function run_tests(): int {
    global $tests;
    $ok = 0; $fail = 0;
    foreach ($tests as [$name, $fn]) {
        try {
            db_begin();
            $fn();
            db_rollback();
            echo "[PASS] $name\n";
            $ok++;
        } catch (Throwable $e) {
            db_rollback();
            echo "[FAIL] $name: " . $e->getMessage() . "\n";
            $fail++;
        }
    }
    echo "\n$ok passed, $fail failed\n";
    return $fail === 0 ? 0 : 1;
}

// Load all test files
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (preg_match('/Test\\.php$/', $file->getFilename())) {
        require $file->getPathname();
    }
}

exit(run_tests());
