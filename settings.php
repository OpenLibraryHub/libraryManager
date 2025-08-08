<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Controllers\AuthController;

AuthMiddleware::require();
Session::start();

$auth = new AuthController();
$message = '';
$success = false;
$tab = $_GET['tab'] ?? 'profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $message = 'Token inválido';
  } else {
    if (($_POST['action'] ?? '') === 'update_profile') {
      $res = $auth->updateProfile($_POST);
      $success = $res['success'];
      $message = $res['message'];
      $tab = 'profile';
    } elseif (($_POST['action'] ?? '') === 'change_password') {
      $res = $auth->changePassword($_POST);
      $success = $res['success'];
      $message = $res['message'];
      $tab = 'password';
    }
  }
}

$currentUser = AuthMiddleware::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Configuración</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Configuración</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="profile.php" class="btn btn-outline-secondary">Mi Perfil</a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $tab==='profile'?'active':'' ?>" href="?tab=profile">Perfil</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='password'?'active':'' ?>" href="?tab=password">Contraseña</a></li>
  </ul>

  <?php if ($tab === 'password'): ?>
    <div class="card"><div class="card-body">
      <h5 class="card-title">Cambiar contraseña</h5>
      <form method="post">
        <?= Session::csrfField() ?>
        <input type="hidden" name="action" value="change_password" />
        <div class="form-group">
          <label>Contraseña actual</label>
          <input type="password" class="form-control" name="current_password" required />
        </div>
        <div class="form-group">
          <label>Nueva contraseña</label>
          <input type="password" class="form-control" name="new_password" minlength="8" required />
        </div>
        <div class="form-group">
          <label>Confirmar nueva contraseña</label>
          <input type="password" class="form-control" name="new_password_confirmation" minlength="8" required />
        </div>
        <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
      </form>
    </div></div>
  <?php else: ?>
    <div class="card"><div class="card-body">
      <h5 class="card-title">Actualizar perfil</h5>
      <form method="post">
        <?= Session::csrfField() ?>
        <input type="hidden" name="action" value="update_profile" />
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nombre</label>
              <input name="first_name" class="form-control" value="<?= htmlspecialchars($currentUser['first_name'] ?? Session::get('user_first_name') ?? '') ?>" required />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Segundo nombre</label>
              <input name="middle_name" class="form-control" value="<?= htmlspecialchars($currentUser['middle_name'] ?? Session::get('user_middle_name') ?? '') ?>" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Apellido paterno</label>
              <input name="paternal_last_name" class="form-control" value="<?= htmlspecialchars($currentUser['paternal_last_name'] ?? Session::get('user_paternal_last_name') ?? '') ?>" required />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Apellido materno</label>
              <input name="maternal_last_name" class="form-control" value="<?= htmlspecialchars($currentUser['maternal_last_name'] ?? Session::get('user_maternal_last_name') ?? '') ?>" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Correo</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? Session::get('user_email') ?? '') ?>" required />
            </div>
          </div>
        </div>
        <hr />
        <div class="alert alert-info">Para cambiar la contraseña desde aquí, ingrese también su contraseña actual.</div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nueva contraseña (opcional)</label>
              <input type="password" name="password" class="form-control" minlength="8" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Contraseña actual (requerida si cambia la contraseña)</label>
              <input type="password" name="current_password" class="form-control" />
            </div>
          </div>
        </div>
        <button class="btn btn-success" type="submit">Guardar cambios</button>
      </form>
    </div></div>
  <?php endif; ?>
</div>
</body>
</html>
