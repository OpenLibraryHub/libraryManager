<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Loan;

AuthMiddleware::require();
Session::start();

$loanModel = new Loan();
$message = '';
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $days = max(1, (int)($_POST['days'] ?? 15));
        $obs = trim((string)($_POST['observation'] ?? ''));
        try {
            if ($loanModel->createLoan($bookId, $userId, $obs, $days)) {
                $success = true;
                $message = 'Préstamo creado.';
            } else {
                $message = 'No se pudo crear el préstamo.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
    }
}

$activeLoans = $loanModel->getActiveLoans();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Préstamos</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Préstamos</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
      <a href="returns.php" class="btn btn-secondary">Devoluciones</a>
    </div>
  </div>
  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Crear préstamo</h5>
      <form method="post">
        <?= Session::csrfField() ?>
        <input type="hidden" name="action" value="create" />
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>ID Libro (interno)</label>
            <input required type="number" name="book_id" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Cédula Usuario</label>
            <input required type="number" name="user_id" class="form-control" />
          </div>
          <div class="form-group col-md-2">
            <label>Días</label>
            <input required type="number" name="days" class="form-control" value="15" min="1" />
          </div>
          <div class="form-group col-md-4">
            <label>Observación</label>
            <input name="observation" class="form-control" />
          </div>
        </div>
        <button class="btn btn-primary" type="submit">Prestar</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Préstamos activos</h5>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="thead-light">
            <tr>
              <th>ID</th><th>Libro</th><th>Autor</th><th>Usuario</th><th>Cédula</th><th>Fecha préstamo</th><th>Fecha límite</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($activeLoans as $l): ?>
            <tr>
              <td><?= (int)$l['PrestamosID'] ?></td>
              <td><?= htmlspecialchars($l['Titulo'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['Autor'] ?? '') ?></td>
              <td><?= htmlspecialchars(($l['Nombre'] ?? '') . ' ' . ($l['Apellido'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)$l['Cedula']) ?></td>
              <td><?= htmlspecialchars($l['fecha_prestamo']) ?></td>
              <td><?= htmlspecialchars($l['fecha_limite']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($activeLoans)): ?>
            <tr><td colspan="7" class="text-center text-muted">Sin préstamos</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
