<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;

AuthMiddleware::require();
Session::start();

$currentUser = AuthMiddleware::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Perfil</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Mi Perfil</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="settings.php" class="btn btn-primary">Configuración</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Nombre</label>
            <input class="form-control" value="<?= htmlspecialchars($currentUser['first_name'] ?? Session::get('user_first_name') ?? '') ?>" readonly />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Segundo nombre</label>
            <input class="form-control" value="<?= htmlspecialchars($currentUser['middle_name'] ?? Session::get('user_middle_name') ?? '') ?>" readonly />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Apellido paterno</label>
            <input class="form-control" value="<?= htmlspecialchars($currentUser['paternal_last_name'] ?? Session::get('user_paternal_last_name') ?? '') ?>" readonly />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Apellido materno</label>
            <input class="form-control" value="<?= htmlspecialchars($currentUser['maternal_last_name'] ?? Session::get('user_maternal_last_name') ?? '') ?>" readonly />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Correo</label>
            <input class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" readonly />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
