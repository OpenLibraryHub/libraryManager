<?php
/**
 * Authentication Middleware
 * 
 * Protects routes that require authentication
 */

namespace App\Middleware;

use App\Helpers\Session;

class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     * 
     * @param bool $redirect Whether to redirect to login if not authenticated
     * @return bool
     */
    public static function check(bool $redirect = true): bool {
        Session::start();
        
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            if ($redirect) {
                self::redirectToLogin();
            }
            return false;
        }
        
        // Check session timeout
        if (!Session::checkTimeout()) {
            Session::flash('error', 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
            if ($redirect) {
                self::redirectToLogin();
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user is guest (not authenticated)
     * 
     * @param bool $redirect Whether to redirect to dashboard if authenticated
     * @return bool
     */
    public static function guest(bool $redirect = true): bool {
        Session::start();
        
        if (Session::isLoggedIn()) {
            if ($redirect) {
                header('Location: /library/dashboard.php');
                exit;
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * Require authentication for current request
     */
    public static function require(): void {
        if (!self::check(true)) {
            exit;
        }
    }
    
    /**
     * Require guest status for current request
     */
    public static function requireGuest(): void {
        if (!self::guest(true)) {
            exit;
        }
    }
    
    /**
     * Check CSRF token for POST requests
     * 
     * @return bool
     */
    public static function verifyCsrf(): bool {
        if (!CSRF_ENABLED) {
            return true;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
        
        return true;
    }
    
    /**
     * Get current authenticated user data
     * 
     * @return array|null
     */
    public static function user(): ?array {
        if (!self::check(false)) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'email' => Session::get('user_email'),
            'name' => Session::get('user_name'),
            'lastname' => Session::get('user_lastname'),
            'first_name' => Session::get('user_first_name'),
            'middle_name' => Session::get('user_middle_name'),
            'paternal_last_name' => Session::get('user_paternal_last_name'),
            'maternal_last_name' => Session::get('user_maternal_last_name'),
        ];
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function userId(): ?int {
        return Session::get('user_id');
    }
    
    /**
     * Check if current user has a specific role
     * 
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool {
        // This is a placeholder - implement based on your role system
        // For now, all authenticated users are considered librarians
        return self::check(false);
    }
    
    /**
     * Redirect to login page
     */
    private static function redirectToLogin(): void {
        // Store the intended URL to redirect back after login
        $currentUrl = $_SERVER['REQUEST_URI'];
        Session::set('intended_url', $currentUrl);
        
        header('Location: /library/login.php');
        exit;
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param array $data
     */
    public static function logActivity(string $action, array $data = []): void {
        $user = self::user();
        if (!$user) {
            return;
        }
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        $logFile = LOGS_PATH . '/activity.log';
        $logEntry = json_encode($logData) . PHP_EOL;
        error_log($logEntry, 3, $logFile);
    }
}
