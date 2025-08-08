<?php
/**
 * Session Management Class
 * 
 * Handles secure session management with CSRF protection
 */

namespace App\Helpers;

class Session {
    private static bool $started = false;
    
    /**
     * Start secure session
     */
    public static function start(): void {
        if (self::$started) {
            return;
        }
        
        // Configure session security
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', SESSION_HTTPONLY);
        ini_set('session.cookie_secure', SESSION_SECURE);
        ini_set('session.cookie_samesite', SESSION_SAMESITE);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);
        ini_set('session.cookie_lifetime', 0); // Session cookie
        ini_set('session.name', SESSION_NAME);
        
        // Start session
        session_start();
        
        self::$started = true;
        
        // Regenerate session ID periodically for security
        if (!self::has('last_regeneration')) {
            self::regenerate();
        } elseif (time() - self::get('last_regeneration') > 300) { // Every 5 minutes
            self::regenerate();
        }
        
        // Initialize CSRF token if not exists
        if (CSRF_ENABLED && !self::has('csrf_token')) {
            self::generateCsrfToken();
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerate(): void {
        session_regenerate_id(true);
        self::set('last_regeneration', time());
    }
    
    /**
     * Set session value
     */
    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Flash message (set once, read once)
     */
    public static function flash(string $key, $value = null) {
        self::start();
        
        if ($value === null) {
            // Get and remove
            $value = self::get("flash_{$key}");
            self::remove("flash_{$key}");
            return $value;
        } else {
            // Set
            self::set("flash_{$key}", $value);
        }
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        self::set('csrf_token_time', time());
        return $token;
    }
    
    /**
     * Get CSRF token
     */
    public static function getCsrfToken(): string {
        self::start();
        
        if (!self::has('csrf_token')) {
            return self::generateCsrfToken();
        }
        
        // Regenerate token if older than 1 hour
        if (time() - self::get('csrf_token_time', 0) > 3600) {
            return self::generateCsrfToken();
        }
        
        return self::get('csrf_token');
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool {
        if (!CSRF_ENABLED) {
            return true;
        }
        
        self::start();
        $sessionToken = self::get('csrf_token');
        
        if (!$sessionToken || !$token) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Get CSRF field HTML
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Get CSRF meta tag HTML
     */
    public static function csrfMeta(): string {
        $token = self::getCsrfToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        self::start();
        return self::has('user_id') && self::has('user_email');
    }
    
    /**
     * Login user
     */
    public static function login(array $userData): void {
        self::start();
        self::regenerate(); // Prevent session fixation
        
        self::set('user_id', $userData['id']);
        self::set('user_email', $userData['email']);
        self::set('user_name', $userData['first_name']);
        self::set('user_lastname', $userData['paternal_last_name']);
        self::set('login_time', time());
        self::set('last_activity', time());
    }
    
    /**
     * Logout user
     */
    public static function logout(): void {
        self::start();
        
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        self::$started = false;
    }
    
    /**
     * Check session timeout
     */
    public static function checkTimeout(): bool {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $lastActivity = self::get('last_activity', 0);
        $timeout = SESSION_LIFETIME * 60; // Convert to seconds
        
        if (time() - $lastActivity > $timeout) {
            self::logout();
            return false;
        }
        
        self::set('last_activity', time());
        return true;
    }
    
    /**
     * Get all session data (for debugging)
     */
    public static function all(): array {
        self::start();
        return $_SESSION;
    }
}
