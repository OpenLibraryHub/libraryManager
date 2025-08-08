<?php
/**
 * PSR-4 Autoloader
 * 
 * Automatically loads PHP classes based on namespace conventions
 */

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = '';
    
    // Base directory for the namespace prefix
    $base_dir = dirname(__DIR__) . '/';
    
    // Handle App namespace
    if (strpos($class, 'App\\') === 0) {
        $class = str_replace('App\\', '', $class);
        $class = str_replace('\\', '/', $class);
        $file = $base_dir . 'app/' . $class . '.php';
    } else {
        // Handle Config namespace
        if (strpos($class, 'Config\\') === 0) {
            $class = str_replace('Config\\', '', $class);
            $class = str_replace('\\', '/', $class);
            $file = $base_dir . 'config/' . $class . '.php';
        } else {
            // Default handling
            $class = str_replace('\\', '/', $class);
            $file = $base_dir . $class . '.php';
        }
    }
    
    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration
require_once __DIR__ . '/config.php';
