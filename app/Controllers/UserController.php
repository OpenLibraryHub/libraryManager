<?php
/**
 * User Controller
 * 
 * Handles user management operations
 */

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use Exception;

class UserController {
    private User $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get all users
     */
    public function index(array $filters = []): array {
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            return ['error' => 'No autorizado', 'users' => []];
        }
        
        if (isset($filters['search']) && isset($filters['field'])) {
            $users = $this->userModel->search($filters['search'], $filters['field']);
        } else {
            $users = $this->userModel->getAllOrdered($filters['order'] ?? 'DESC');
        }
        
        return ['users' => $users];
    }
    
    /**
     * Get single user
     */
    public function show($idNumber): array {
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            return ['error' => 'No autorizado', 'user' => null];
        }
        
        $user = $this->userModel->find($idNumber);
        
        if (!$user) {
            return ['error' => 'Usuario no encontrado', 'user' => null];
        }
        
        // Get user's active loans
        $loanModel = new \App\Models\Loan();
        $user['active_loans'] = $loanModel->getUserActiveLoans($idNumber);
        
        return ['user' => $user];
    }
    
    /**
     * Create new user
     */
    public function create(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        
        // Validate input
        $errors = $this->userModel->validate($data);
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input (English keys expected)
        $sanitized = Validator::sanitizeArray($data, [
            'user_key' => 'int',
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'email',
            'id_number' => 'int',
            'phone' => 'int',
            'address' => 'string'
        ]);
        // Optional fields cleanup
        if (empty($sanitized['phone'])) { unset($sanitized['phone']); }
        if (empty($sanitized['address'])) { unset($sanitized['address']); }
        if (empty($sanitized['phone'])) { unset($sanitized['phone']); }
        
        // Check if user already exists
        if ($this->userModel->userExists(
            $sanitized['email'] ?? '',
            $sanitized['id_number'] ?? 0,
            $sanitized['user_key'] ?? 0,
            $sanitized['phone'] ?? null
        )) {
            $response['message'] = 'El usuario ya existe (correo, cédula, llave o teléfono duplicado).';
            return $response;
        }
        
        try {
            // Create user
            $newUser = $this->userModel->createUser($sanitized);
            
            if ($newUser) {
                AuthMiddleware::logActivity('user_create', [
                    'cedula' => $sanitized['id_number'],
                    'email' => $sanitized['email']
                ]);
                
                $response['success'] = true;
                $response['message'] = 'Usuario registrado satisfactoriamente.';
                $response['data'] = $newUser;
            } else {
                $response['message'] = 'Error al registrar el usuario.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar la solicitud.';
            error_log('User creation error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Update user
     */
    public function update($idNumber, array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        
        // Check if user exists
        if (!$this->userModel->find($idNumber)) {
            $response['message'] = 'Usuario no encontrado.';
            return $response;
        }
        
        // Validate input
        $errors = $this->userModel->validate($data, true);
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input
        $sanitized = Validator::sanitizeArray($data, [
            'user_key' => 'int',
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'email',
            'phone' => 'int',
            'address' => 'string'
        ]);
        
        // If changing cedula, check if new cedula is available
        if (isset($sanitized['id_number']) && $sanitized['id_number'] != $idNumber) {
            if ($this->userModel->userExists('', $sanitized['id_number'], '', null)) {
                $response['errors']['id_number'] = ['Esta cédula ya está en uso.'];
                $response['message'] = 'La cédula ya está registrada.';
                return $response;
            }
        }
        
        try {
            // Update user
            if ($this->userModel->updateUser($idNumber, $sanitized)) {
                AuthMiddleware::logActivity('user_update', ['cedula' => $idNumber]);
                
                $response['success'] = true;
                $response['message'] = 'Usuario actualizado exitosamente.';
            } else {
                $response['message'] = 'No se realizaron cambios.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar la solicitud.';
            error_log('User update error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Delete user
     */
    public function delete($idNumber): array {
        $response = ['success' => false, 'message' => ''];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        // Require admin role
        if (!AuthMiddleware::hasRole('admin')) {
            $response['message'] = 'No autorizado (se requiere administrador).';
            return $response;
        }
        
        // Check if user has active loans
        $loanModel = new \App\Models\Loan();
        $activeLoans = $loanModel->getUserActiveLoans($idNumber);
        
        if (!empty($activeLoans)) {
            $response['message'] = 'No se puede eliminar el usuario porque tiene préstamos activos.';
            return $response;
        }
        
        try {
            if ($this->userModel->delete($idNumber)) {
                AuthMiddleware::logActivity('user_delete', ['cedula' => $idNumber]);
                
                $response['success'] = true;
                $response['message'] = 'Usuario eliminado exitosamente.';
            } else {
                $response['message'] = 'No se pudo eliminar el usuario.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar la solicitud.';
            error_log('User deletion error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Sanction user
     */
    public function sanction($idNumber): array {
        $response = ['success' => false, 'message' => ''];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        // Require admin role
        if (!AuthMiddleware::hasRole('admin')) {
            $response['message'] = 'No autorizado (se requiere administrador).';
            return $response;
        }
        
        try {
            if ($this->userModel->sanction($idNumber)) {
                AuthMiddleware::logActivity('user_sanction', ['cedula' => $idNumber]);
                
                $response['success'] = true;
                $response['message'] = 'Usuario sancionado exitosamente.';
            } else {
                $response['message'] = 'No se pudo sancionar al usuario.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar la solicitud.';
            error_log('User sanction error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Remove user sanction
     */
    public function removeSanction($idNumber): array {
        $response = ['success' => false, 'message' => ''];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        // Require admin role
        if (!AuthMiddleware::hasRole('admin')) {
            $response['message'] = 'No autorizado (se requiere administrador).';
            return $response;
        }
        
        try {
            if ($this->userModel->removeSanction($idNumber)) {
                AuthMiddleware::logActivity('user_sanction_remove', ['cedula' => $idNumber]);
                
                $response['success'] = true;
                $response['message'] = 'Sanción removida exitosamente.';
            } else {
                $response['message'] = 'No se pudo remover la sanción.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar la solicitud.';
            error_log('Remove sanction error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Get user statistics
     */
    public function statistics(): array {
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            return ['error' => 'No autorizado', 'stats' => []];
        }
        
        return ['stats' => $this->userModel->getStatistics()];
    }
    
    /**
     * Export users to array (for Excel export)
     */
    public function export(): array {
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            return ['error' => 'No autorizado', 'users' => []];
        }
        
        AuthMiddleware::logActivity('users_export');
        
        return ['users' => $this->userModel->all()];
    }
}
