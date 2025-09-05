<?php
/**
 * Reset Password Page
 * 
 * Allows users to reset their password using a token
 */

require_once 'config/autoload.php';

use App\Controllers\AuthController;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Librarian;

// Redirect if already logged in
AuthMiddleware::requireGuest();

Session::start();

$errors = [];
$message = Session::flash('error');
$success = Session::flash('success');

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    Session::flash('error', 'No estás autorizado.');
    header('Location: login.php');
    exit;
}

// Validate token before rendering the form
$librarianModel = new Librarian();
$librarianByToken = $librarianModel->findByResetToken($token);
if (!$librarianByToken) {
    Session::flash('error', 'No estás autorizado.');
    header('Location: login.php');
    exit;
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de seguridad inválido. Por favor, recargue la página.';
    } else {
        $authController = new AuthController();
        $result = $authController->resetPassword(array_merge($_POST, ['token' => $token]));
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $message = $result['message'];
            $errors = $result['errors'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema Biblioteca</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?= Session::csrfMeta() ?>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .reset-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .reset-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .reset-body {
            padding: 40px 30px;
        }
        .form-group label {
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-back {
            background: #6c757d;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .alert-danger {
            background-color: #fee;
            color: #c33;
        }
        .alert-success {
            background-color: #efe;
            color: #3c3;
        }
        .invalid-feedback {
            display: block;
            margin-top: 5px;
            color: #dc3545;
            font-size: 13px;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle .toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>RESTABLECER CONTRASEÑA</h1>
            <p>Ingresa tu nueva contraseña</p>
        </div>
        
        <div class="reset-body">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="reset-password.php?token=<?= htmlspecialchars($token) ?>" id="resetForm">
                <?= Session::csrfField() ?>
                
                <div class="form-group">
                    <label for="new_password">Nueva contraseña</label>
                    <div class="password-toggle">
                        <input 
                            type="password" 
                            name="new_password" 
                            class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                            id="new_password" 
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                        <button type="button" class="toggle-btn" onclick="togglePassword('new_password')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['new_password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['new_password'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="new_password_confirmation">Confirmar nueva contraseña</label>
                    <div class="password-toggle">
                        <input 
                            type="password" 
                            name="new_password_confirmation" 
                            class="form-control <?= isset($errors['new_password_confirmation']) ? 'is-invalid' : '' ?>" 
                            id="new_password_confirmation" 
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                        <button type="button" class="toggle-btn" onclick="togglePassword('new_password_confirmation')">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['new_password_confirmation'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['new_password_confirmation'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-reset">
                    Restablecer contraseña
                </button>
            </form>
            
            <a href="login.php" class="btn-back">
                Volver al inicio de sesión
            </a>
            
            <div class="footer-text">
                <p>La contraseña debe tener al menos 8 caracteres</p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
