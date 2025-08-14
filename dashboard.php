<?php
/**
 * Dashboard - Main Application Page
 * 
 * Secure dashboard with all security features implemented
 */

require_once 'config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use App\Helpers\Validator;

// Require authentication
AuthMiddleware::require();

// Get current user
$currentUser = AuthMiddleware::user();

// Initialize models for statistics
$userModel = new User();
$bookModel = new Book();
$loanModel = new Loan();

// Get statistics
$userStats = $userModel->getStatistics();
$bookStats = $bookModel->getStatistics();
$loanStats = $loanModel->getStatistics();

// Get recent activities
$recentLoans = array_slice($loanModel->getActiveLoans(), 0, 5);
$overdueLoans = array_slice($loanModel->getOverdueLoans(), 0, 5);
$recentReturns = array_slice($loanModel->getReturnedLoans(), 0, 5);
$dueSoonLoans = array_slice($loanModel->getDueSoonLoans(3), 0, 5);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Biblioteca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?= Session::csrfMeta() ?>
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #48bb78;
            --danger-color: #f56565;
            --warning-color: #ed8936;
            --dark-color: #2d3748;
        }
        
        body {
            background-color: #f7fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: #f1f5f9;
            color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        
        .stat-card.primary .stat-icon {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .stat-card.success .stat-icon {
            background: var(--success-color);
            color: white;
        }
        
        .stat-card.warning .stat-icon {
            background: var(--warning-color);
            color: white;
        }
        
        .stat-card.danger .stat-icon {
            background: var(--danger-color);
            color: white;
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .table-card h5 {
            margin-bottom: 20px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-danger {
            background-color: var(--danger-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-menu .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-book"></i> Sistema Biblioteca
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= Validator::escape($currentUser['name']) ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="logout.php" class="d-inline">
                                    <?= Session::csrfField() ?>
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-book"></i> Libros
                    </a>
                    <a class="nav-link" href="loans.php">
                        <i class="fas fa-hand-holding"></i> Préstamos
                    </a>
                    <a class="nav-link" href="returns.php">
                        <i class="fas fa-undo"></i> Devoluciones
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                     <a class="nav-link" href="holds.php">
                         <i class="fas fa-list"></i> Lista de espera
                     </a>
                     <a class="nav-link" href="due_soon.php">
                         <i class="fas fa-clock"></i> Por vencer
                     </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Welcome Message -->
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> Bienvenido al nuevo sistema seguro de biblioteca. 
                    Todas las vulnerabilidades críticas han sido corregidas.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card primary">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h3><?= number_format($userStats['total']) ?></h3>
                                    <p class="text-muted mb-0">Usuarios Totales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <h3><?= number_format($bookStats['total_titles']) ?></h3>
                                    <p class="text-muted mb-0">Libros en Catálogo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon">
                                    <i class="fas fa-hand-holding"></i>
                                </div>
                                <div>
                                    <h3><?= number_format($loanStats['active']) ?></h3>
                                    <p class="text-muted mb-0">Préstamos Activos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card danger">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h3><?= number_format($loanStats['overdue']) ?></h3>
                                    <p class="text-muted mb-0">Préstamos Vencidos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Loans -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5><i class="fas fa-clock"></i> Préstamos Recientes</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Libro</th>
                                            <th>Usuario</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentLoans as $loan): ?>
                                        <tr>
                                            <td><?= Validator::escape($loan['title'] ?? '') ?></td>
                                            <td><?= Validator::escape(trim(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? ''))) ?></td>
                                            <td><?= isset($loan['loaned_at']) ? date('d/m/Y', strtotime($loan['loaned_at'])) : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5><i class="fas fa-exclamation-circle text-danger"></i> Préstamos Vencidos</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Libro</th>
                                            <th>Usuario</th>
                                            <th>Días Vencido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($overdueLoans as $loan): ?>
                                        <tr>
                                            <td><?= Validator::escape($loan['title'] ?? '') ?></td>
                                            <td><?= Validator::escape(trim(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? ''))) ?></td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?= (int)($loan['days_overdue'] ?? 0) ?> días
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recently Returned Books -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5><i class="fas fa-undo"></i> Libros devueltos recientemente</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Libro</th>
                                            <th>Usuario</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentReturns as $loan): ?>
                                        <tr>
                                            <td><?= Validator::escape($loan['title'] ?? '') ?></td>
                                            <td><?= Validator::escape(trim(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? ''))) ?></td>
                                            <td><?= isset($loan['returned_at']) ? date('d/m/Y', strtotime($loan['returned_at'])) : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-card">
                            <h5><i class="fas fa-hourglass-half"></i> Próximos a vencer (3 días)</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Libro</th>
                                            <th>Usuario</th>
                                            <th>Fecha límite</th>
                                            <th>Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dueSoonLoans as $loan): ?>
                                        <tr>
                                            <td><?= Validator::escape($loan['title'] ?? '') ?></td>
                                            <td><?= Validator::escape(trim(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? ''))) ?></td>
                                            <td><?= isset($loan['due_at']) ? date('d/m/Y', strtotime($loan['due_at'])) : '-' ?></td>
                                            <td><span class="badge badge-warning"><?= (int)($loan['days_left'] ?? 0) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh statistics every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
