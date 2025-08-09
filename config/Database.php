<?php
/**
 * Database Connection Class
 * 
 * Provides a secure singleton database connection using MySQLi
 * with prepared statements support.
 */

namespace Config;

use mysqli;
use Exception;

class Database {
    private static ?Database $instance = null;
    private ?mysqli $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection(): mysqli {
        if ($this->connection === null || !$this->connection->ping()) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void {
        try {
            // Create connection
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset(DB_CHARSET);
            
            // Set SQL mode for better security
            $this->connection->query("SET sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            
            if (APP_DEBUG) {
                die("Database connection error: " . $e->getMessage());
            } else {
                die("A database error occurred. Please try again later.");
            }
        }
    }
    
    /**
     * Execute a prepared query
     * 
     * @param string $sql SQL query with placeholders
     * @param string $types Types string (i=integer, s=string, d=double, b=blob)
     * @param array $params Parameters to bind
     * @return mysqli_stmt|false
     */
    public function prepare(string $sql, string $types = '', array $params = []) {
        $connection = $this->getConnection();
        
        $stmt = $connection->prepare($sql);
        
        if ($stmt === false) {
            $this->logError("Prepare failed: " . $connection->error . " SQL: " . $sql);
            return false;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        return $stmt;
    }
    
    /**
     * Execute a query and return results
     * 
     * @param string $sql SQL query
     * @param string $types Types string
     * @param array $params Parameters
     * @return array|false
     */
    public function query(string $sql, string $types = '', array $params = []) {
        $stmt = $this->prepare($sql, $types, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        if (!$stmt->execute()) {
            $this->logError("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        
        if ($result === false) {
            return true; // For INSERT, UPDATE, DELETE
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $data;
    }
    
    /**
     * Execute a query and return single row
     */
    public function queryOne(string $sql, string $types = '', array $params = []) {
        $result = $this->query($sql, $types, $params);
        if ($result === false) {
            // Query error
            return false;
        }
        if (empty($result)) {
            // No rows found
            return null;
        }
        return $result[0];
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId(): int {
        return $this->getConnection()->insert_id;
    }
    
    /**
     * Get affected rows
     */
    public function affectedRows(): int {
        return $this->getConnection()->affected_rows;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->getConnection()->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->getConnection()->rollback();
    }
    
    /**
     * Escape string for safe use in queries (use prepared statements instead when possible)
     */
    public function escape(string $string): string {
        return $this->getConnection()->real_escape_string($string);
    }
    
    /**
     * Log error to file
     */
    private function logError(string $message): void {
        $logFile = LOGS_PATH . '/database_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Close connection
     */
    public function close(): void {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
