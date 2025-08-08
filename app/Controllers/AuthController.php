<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication and session management
 */

namespace App\Controllers;

use App\Models\Librarian;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;

class AuthController {
    private Librarian $librarianModel;
    
    public function __construct() {
        $this->librarianModel = new Librarian();
    }
    
    /**
     * Handle login request
     */
    public function login(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        if (!$validator->validate($data, $rules)) {
            $response['errors'] = $validator->getErrors();
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input
        $email = Validator::sanitizeEmail($data['email']);
        $password = $data['password'];
        
        // Attempt authentication
        $librarian = $this->librarianModel->authenticate($email, $password);
        
        if ($librarian) {
            // Login successful
            Session::login($librarian);
            // Ensure detailed profile fields are present in session for profile/settings pages
            Session::set('user_email', $librarian['email'] ?? '');
            Session::set('user_name', $librarian['first_name'] ?? '');
            Session::set('user_lastname', $librarian['paternal_last_name'] ?? '');
            Session::set('user_first_name', $librarian['first_name'] ?? '');
            Session::set('user_middle_name', $librarian['middle_name'] ?? '');
            Session::set('user_paternal_last_name', $librarian['paternal_last_name'] ?? '');
            Session::set('user_maternal_last_name', $librarian['maternal_last_name'] ?? '');
            AuthMiddleware::logActivity('login', ['email' => $email]);
            $this->librarianModel->logLoginAttempt($email, true);
            
            $response['success'] = true;
            $response['message'] = 'Inicio de sesión exitoso.';
            $response['redirect'] = Session::get('intended_url', '/library/dashboard.php');
            Session::remove('intended_url');
        } else {
            // Login failed
            $this->librarianModel->logLoginAttempt($email, false);
            $response['message'] = 'Credenciales incorrectas.';
        }
        
        return $response;
    }
    
    /**
     * Handle logout request
     */
    public function logout(): void {
        if (AuthMiddleware::check(false)) {
            AuthMiddleware::logActivity('logout');
        }
        
        Session::logout();
        header('Location: /library/login.php');
        exit;
    }
    
    /**
     * Handle profile update
     */
    public function updateProfile(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        
        $userId = AuthMiddleware::userId();
        
        // Validate input
        $errors = $this->librarianModel->validate($data, true);
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input
        $sanitized = Validator::sanitizeArray($data, [
            'first_name' => 'string',
            'paternal_last_name' => 'string',
            'maternal_last_name' => 'string',
            'middle_name' => 'string',
            'email' => 'email',
            'password' => 'raw'
        ]);
        
        // Check if email is already taken by another user (only if changed)
        $currentUser = AuthMiddleware::user();
        $emailChanged = isset($sanitized['email']) && isset($currentUser['email']) && $sanitized['email'] !== $currentUser['email'];
        if ($emailChanged && $this->librarianModel->emailExists($sanitized['email'], $userId)) {
            $response['errors']['email'] = ['Este correo ya está en uso.'];
            $response['message'] = 'El correo electrónico ya está registrado.';
            return $response;
        }
        
        // If password is provided, verify current password first
        if (!empty($sanitized['password'])) {
            if (empty($data['current_password'])) {
                $response['errors']['current_password'] = ['Debe proporcionar la contraseña actual.'];
                $response['message'] = 'Por favor, ingrese su contraseña actual.';
                return $response;
            }
            
            if (!$this->librarianModel->verifyPassword($userId, $data['current_password'])) {
                $response['errors']['current_password'] = ['La contraseña actual es incorrecta.'];
                $response['message'] = 'La contraseña actual no es válida.';
                return $response;
            }
        }
        
        // Update profile
        if ($this->librarianModel->updateLibrarian($userId, $sanitized)) {
            // Update session data
            Session::set('user_email', $sanitized['email']);
            Session::set('user_name', $sanitized['first_name']);
            Session::set('user_lastname', $sanitized['paternal_last_name']);
            // Also store detailed name fields for profile consumption
            Session::set('user_first_name', $sanitized['first_name']);
            Session::set('user_middle_name', $sanitized['middle_name'] ?? '');
            Session::set('user_paternal_last_name', $sanitized['paternal_last_name']);
            Session::set('user_maternal_last_name', $sanitized['maternal_last_name'] ?? '');
            
            AuthMiddleware::logActivity('profile_update');
            
            $response['success'] = true;
            $response['message'] = 'Perfil actualizado exitosamente.';
        } else {
            $response['message'] = 'No se pudo actualizar el perfil.';
        }
        
        return $response;
    }
    
    /**
     * Handle password change
     */
    public function changePassword(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Check authentication
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        
        $userId = AuthMiddleware::userId();
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'required'
        ];
        
        if (!$validator->validate($data, $rules)) {
            $response['errors'] = $validator->getErrors();
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Check password confirmation
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            $response['errors']['new_password'] = ['Las contraseñas no coinciden.'];
            $response['message'] = 'La confirmación de contraseña no coincide.';
            return $response;
        }
        
        // Verify current password
        if (!$this->librarianModel->verifyPassword($userId, $data['current_password'])) {
            $response['errors']['current_password'] = ['La contraseña actual es incorrecta.'];
            $response['message'] = 'La contraseña actual no es válida.';
            return $response;
        }
        
        // Update password
        if ($this->librarianModel->updatePassword($userId, $data['new_password'])) {
            AuthMiddleware::logActivity('password_change');
            
            $response['success'] = true;
            $response['message'] = 'Contraseña actualizada exitosamente.';
        } else {
            $response['message'] = 'No se pudo actualizar la contraseña.';
        }
        
        return $response;
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'email' => 'required|email'
        ];
        
        if (!$validator->validate($data, $rules)) {
            $response['errors'] = $validator->getErrors();
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input
        $email = Validator::sanitizeEmail($data['email']);
        
        // Check if email exists
        $librarian = $this->librarianModel->findByEmail($email);
        
        if (!$librarian) {
            // Don't reveal if email exists or not for security
            $response['success'] = true;
            $response['message'] = 'Si el correo existe en nuestro sistema, recibirás un enlace de restablecimiento.';
            return $response;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        if ($this->librarianModel->storeResetToken($librarian['id'], $token, $expires)) {
            // Send email (you'll need to implement email functionality)
            $resetLink = APP_URL . '/reset-password.php?token=' . $token;
            
            // For now, just log the reset request
            AuthMiddleware::logActivity('password_reset_requested', ['email' => $email]);
            
            $response['success'] = true;
            $response['message'] = 'Se ha enviado un enlace de restablecimiento a tu correo electrónico.';
        } else {
            $response['message'] = 'No se pudo procesar la solicitud. Inténtalo de nuevo.';
        }
        
        return $response;
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Validate input
        $validator = new Validator();
        $rules = [
            'token' => 'required',
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'required'
        ];
        
        if (!$validator->validate($data, $rules)) {
            $response['errors'] = $validator->getErrors();
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Check password confirmation
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            $response['errors']['new_password'] = ['Las contraseñas no coinciden.'];
            $response['message'] = 'La confirmación de contraseña no coincide.';
            return $response;
        }
        
        // Verify token and get user
        $librarian = $this->librarianModel->findByResetToken($data['token']);
        
        if (!$librarian) {
            $response['message'] = 'El enlace de restablecimiento es inválido o ha expirado.';
            return $response;
        }
        
        // Update password and clear token
        if ($this->librarianModel->resetPasswordWithToken($librarian['id'], $data['new_password'])) {
            AuthMiddleware::logActivity('password_reset_completed', ['email' => $librarian['email']]);
            
            $response['success'] = true;
            $response['message'] = 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.';
        } else {
            $response['message'] = 'No se pudo restablecer la contraseña. Inténtalo de nuevo.';
        }
        
        return $response;
    }
    
    /**
     * Create new librarian (admin only)
     */
    public function createLibrarian(array $data): array {
        $response = ['success' => false, 'message' => '', 'errors' => []];
        
        // Check authentication and authorization
        if (!AuthMiddleware::check(false)) {
            $response['message'] = 'No autorizado.';
            return $response;
        }
        
        // Validate input
        $errors = $this->librarianModel->validate($data);
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Por favor, corrija los errores en el formulario.';
            return $response;
        }
        
        // Sanitize input
        $sanitized = Validator::sanitizeArray($data, [
            'first_name' => 'string',
            'paternal_last_name' => 'string',
            'maternal_last_name' => 'string',
            'middle_name' => 'string',
            'email' => 'email',
            'password' => 'raw'
        ]);
        
        // Check if email already exists
        if ($this->librarianModel->emailExists($sanitized['email'])) {
            $response['errors']['email'] = ['Este correo ya está en uso.'];
            $response['message'] = 'El correo electrónico ya está registrado.';
            return $response;
        }
        
        // Create librarian
        $newLibrarian = $this->librarianModel->createLibrarian($sanitized);
        
        if ($newLibrarian) {
            AuthMiddleware::logActivity('librarian_create', ['email' => $sanitized['email']]);
            
            $response['success'] = true;
            $response['message'] = 'Bibliotecario creado exitosamente.';
            $response['data'] = $newLibrarian;
        } else {
            $response['message'] = 'No se pudo crear el bibliotecario.';
        }
        
        return $response;
    }
}
