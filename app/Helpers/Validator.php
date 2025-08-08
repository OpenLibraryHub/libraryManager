<?php
/**
 * Input Validation and Sanitization Class
 * 
 * Provides methods for validating and sanitizing user input
 */

namespace App\Helpers;

class Validator {
    private array $errors = [];
    private array $data = [];
    
    /**
     * Validate input data
     * 
     * @param array $data Input data to validate
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    public function validate(array $data, array $rules): bool {
        $this->errors = [];
        $this->data = $data;
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $ruleSet);
            
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Apply validation rule
     */
    private function applyRule(string $field, $value, string $rule): void {
        $params = [];
        
        // Check if rule has parameters (e.g., min:3)
        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }
        
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "El campo {$field} es obligatorio.");
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El campo {$field} debe ser un email válido.");
                }
                break;
                
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, "El campo {$field} debe ser numérico.");
                }
                break;
                
            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "El campo {$field} debe ser un número entero.");
                }
                break;
                
            case 'min':
                $min = $params[0] ?? 0;
                if ($value && strlen($value) < $min) {
                    $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres.");
                }
                break;
                
            case 'max':
                $max = $params[0] ?? PHP_INT_MAX;
                if ($value && strlen($value) > $max) {
                    $this->addError($field, "El campo {$field} no puede tener más de {$max} caracteres.");
                }
                break;
                
            case 'between':
                $min = $params[0] ?? 0;
                $max = $params[1] ?? PHP_INT_MAX;
                $len = strlen($value);
                if ($value && ($len < $min || $len > $max)) {
                    $this->addError($field, "El campo {$field} debe tener entre {$min} y {$max} caracteres.");
                }
                break;
                
            case 'alpha':
                if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->addError($field, "El campo {$field} solo puede contener letras.");
                }
                break;
                
            case 'alphanumeric':
                if ($value && !ctype_alnum(str_replace(' ', '', $value))) {
                    $this->addError($field, "El campo {$field} solo puede contener letras y números.");
                }
                break;
                
            case 'regex':
                $pattern = $params[0] ?? '';
                if ($value && !preg_match($pattern, $value)) {
                    $this->addError($field, "El campo {$field} tiene un formato inválido.");
                }
                break;
                
            case 'unique':
                // This would need database access
                // Implement in child class or pass callback
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, "La confirmación de {$field} no coincide.");
                }
                break;
                
            case 'date':
                if ($value && !strtotime($value)) {
                    $this->addError($field, "El campo {$field} debe ser una fecha válida.");
                }
                break;
                
            case 'after':
                $afterDate = $params[0] ?? 'now';
                if ($value && strtotime($value) <= strtotime($afterDate)) {
                    $this->addError($field, "El campo {$field} debe ser posterior a {$afterDate}.");
                }
                break;
                
            case 'before':
                $beforeDate = $params[0] ?? 'now';
                if ($value && strtotime($value) >= strtotime($beforeDate)) {
                    $this->addError($field, "El campo {$field} debe ser anterior a {$beforeDate}.");
                }
                break;
                
            case 'isbn':
                if ($value && !$this->validateISBN($value)) {
                    $this->addError($field, "El campo {$field} debe ser un ISBN válido.");
                }
                break;
                
            case 'phone':
                if ($value && !preg_match('/^[0-9\-\+\(\)\s]+$/', $value)) {
                    $this->addError($field, "El campo {$field} debe ser un número de teléfono válido.");
                }
                break;
        }
    }
    
    /**
     * Validate ISBN
     */
    private function validateISBN(string $isbn): bool {
        $isbn = str_replace(['-', ' '], '', $isbn);
        
        // ISBN-10
        if (strlen($isbn) === 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                if (!is_numeric($isbn[$i])) return false;
                $sum += (int)$isbn[$i] * (10 - $i);
            }
            $checkDigit = $isbn[9];
            if ($checkDigit === 'X') $checkDigit = 10;
            elseif (!is_numeric($checkDigit)) return false;
            else $checkDigit = (int)$checkDigit;
            
            return ($sum + $checkDigit) % 11 === 0;
        }
        
        // ISBN-13
        if (strlen($isbn) === 13) {
            if (!is_numeric($isbn)) return false;
            
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$isbn[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            return (int)$isbn[12] === $checkDigit;
        }
        
        return false;
    }
    
    /**
     * Add error message
     */
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get all errors
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Get errors for specific field
     */
    public function getFieldErrors(string $field): array {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Check if field has errors
     */
    public function hasFieldError(string $field): bool {
        return isset($this->errors[$field]);
    }
    
    /**
     * Get first error message
     */
    public function getFirstError(): ?string {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
    
    /**
     * Sanitize input string
     */
    public static function sanitizeString(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    
    /**
     * Sanitize email
     */
    public static function sanitizeEmail(string $email): string {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return strtolower(trim($email));
    }
    
    /**
     * Sanitize integer
     */
    public static function sanitizeInt($input): ?int {
        $filtered = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        return $filtered !== false ? (int)$filtered : null;
    }
    
    /**
     * Sanitize float
     */
    public static function sanitizeFloat($input): ?float {
        $filtered = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return $filtered !== false ? (float)$filtered : null;
    }
    
    /**
     * Sanitize URL
     */
    public static function sanitizeUrl(string $url): string {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize array of data
     */
    public static function sanitizeArray(array $data, array $rules = []): array {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $rule = $rules[$key] ?? 'string';
            
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                switch ($rule) {
                    case 'email':
                        $sanitized[$key] = self::sanitizeEmail($value);
                        break;
                    case 'int':
                    case 'integer':
                        $sanitized[$key] = self::sanitizeInt($value);
                        break;
                    case 'float':
                    case 'double':
                        $sanitized[$key] = self::sanitizeFloat($value);
                        break;
                    case 'url':
                        $sanitized[$key] = self::sanitizeUrl($value);
                        break;
                    case 'raw':
                        $sanitized[$key] = $value;
                        break;
                    default:
                        $sanitized[$key] = self::sanitizeString($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Clean input for database storage (use with prepared statements)
     */
    public static function cleanForDb(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        return $input;
    }
    
    /**
     * Escape output for HTML display
     */
    public static function escape($input): string {
        if (is_array($input)) {
            return array_map([self::class, 'escape'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
