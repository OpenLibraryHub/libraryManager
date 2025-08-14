<?php
/**
 * Login Page
 * 
 * Secure login implementation with CSRF protection
 */

require_once 'config/autoload.php';

use App\Controllers\AuthController;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;

// Redirect if already logged in
AuthMiddleware::requireGuest();

Session::start();

$errors = [];
$message = Session::flash('error');
$success = Session::flash('success');

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de seguridad inválido. Por favor, recargue la página.';
    } else {
        $authController = new AuthController();
        $result = $authController->login($_POST);
        
        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit;
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
    <title>Inicio de sesión - Sistema Biblioteca</title>
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
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .login-body {
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
        .btn-login {
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
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
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
    <div class="login-container">
        <div class="login-header">
            <h1>SISTEMA BIBLIOTECA</h1>
            <p>Gestión de Préstamos de Libros</p>
        </div>
        
        <div class="login-body">
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
            
            <form method="POST" action="login.php" id="loginForm">
                <?= Session::csrfField() ?>
                
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                        id="email" 
                        placeholder="correo@ejemplo.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autofocus
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['email'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-toggle">
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                            id="password" 
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="toggle-btn" onclick="togglePassword()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['password'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    Iniciar sesión
                </button>
            </form>

            <hr>
            <h6 class="text-muted">Acceso de usuarios</h6>
            <form method="POST" action="user_login.php" id="patronLogin">
              <?= Session::csrfField() ?>
              <div class="form-group">
                <label for="id_number">Cédula</label>
                <input type="number" name="id_number" class="form-control" required />
              </div>
              <div class="form-group">
                <label for="user_key">Llave</label>
                <input type="number" name="user_key" class="form-control" required />
              </div>
              <button type="submit" class="btn btn-outline-primary btn-login">Entrar</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="forgot-password.php" class="text-muted" style="text-decoration: none; font-size: 14px;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
            
            <div class="footer-text">
                <p>Sistema seguro con protección CSRF</p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
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
