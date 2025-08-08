<?php
/**
 * Application Configuration
 * 
 * This file contains all configuration settings for the application.
 * It loads environment variables and provides default values.
 */

// Load environment variables if .env file exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_DATABASE'] ?? 'Library');
define('DB_USER', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Library Management System');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/library');
define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Security Configuration
define('APP_KEY', $_ENV['APP_KEY'] ?? bin2hex(random_bytes(16)));
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 120);
define('CSRF_ENABLED', filter_var($_ENV['CSRF_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

// Paths Configuration
define('VIEWS_PATH', APP_ROOT . '/resources/views');
define('LOGS_PATH', APP_ROOT . '/logs');
define('PUBLIC_PATH', APP_ROOT . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Session Configuration
define('SESSION_NAME', 'library_session');
define('SESSION_SECURE', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');

// Timezone
date_default_timezone_set('America/Bogota');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Log errors
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/error.log');
