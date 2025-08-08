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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $loanId = (int)($_POST['loan_id'] ?? 0);
        try {
            if ($loanModel->returnLoan($loanId)) {
                $success = true;
                $message = 'Libro devuelto.';
            } else {
                $message = 'No se pudo devolver el libro.';
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
  <title>Devoluciones</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Devoluciones</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
      <a href="loans.php" class="btn btn-primary">Préstamos</a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>ID Préstamo</th>
            <th>Libro</th>
            <th>Usuario</th>
            <th>Fecha préstamo</th>
            <th>Fecha límite</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($activeLoans as $l): ?>
          <tr>
            <td><?= (int)$l['PrestamosID'] ?></td>
            <td><?= htmlspecialchars(($l['Titulo'] ?? '') . ' - ' . ($l['Autor'] ?? '')) ?></td>
            <td><?= htmlspecialchars(($l['Nombre'] ?? '') . ' ' . ($l['Apellido'] ?? '') . ' (' . ($l['Cedula'] ?? '') . ')') ?></td>
            <td><?= htmlspecialchars($l['fecha_prestamo']) ?></td>
            <td><?= htmlspecialchars($l['fecha_limite']) ?></td>
            <td>
              <form method="post" class="mb-0">
                <?= Session::csrfField() ?>
                <input type="hidden" name="action" value="return" />
                <input type="hidden" name="loan_id" value="<?= (int)$l['PrestamosID'] ?>" />
                <button class="btn btn-sm btn-success" type="submit">Marcar devuelto</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($activeLoans)): ?>
          <tr><td colspan="6" class="text-center text-muted">Sin préstamos activos</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
