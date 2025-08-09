<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Hold;

AuthMiddleware::require();
Session::start();

$loanModel = new Loan();
$bookModel = new Book();
$holdModel = new Hold();
$message = '';
$success = false;
$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $loanId = (int)($_POST['loan_id'] ?? 0);
        try {
            if ($loanModel->returnLoan($loanId)) {
                $success = true;
                $message = 'Libro devuelto.';
                // Auto-asignar al siguiente en lista de espera
                $loan = $loanModel->find($loanId);
                if ($loan) {
                    $bookId = (int)$loan['book_id'];
                    $next = $holdModel->nextInQueue($bookId);
                    if ($next) {
                        try {
                            if ($loanModel->createLoan($bookId, (int)$next['user_id'], 'Asignado desde lista de espera', 15)) {
                                $holdModel->markFulfilled((int)$next['id']);
                                $message .= ' Asignado al siguiente en la lista.';
                            }
                        } catch (\Exception $e) {
                            // dejar mensaje informativo
                            $message .= ' (No se pudo asignar al siguiente: ' . htmlspecialchars($e->getMessage()) . ')';
                        }
                    }
                }
            } else {
                $message = 'No se pudo devolver el libro.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
    }
}

$activeLoans = $q !== '' ? $loanModel->searchLoans($q, $field, true) : $loanModel->getActiveLoans();
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

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Buscar préstamos activos</h5>
      <form method="get" class="form-inline">
        <div class="form-row align-items-end w-100">
          <div class="col-md-5 mb-2">
            <label class="small text-muted d-block">Búsqueda</label>
            <input class="form-control" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Libro, usuario o cédula">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small text-muted d-block">Campo</label>
            <select class="form-control" name="field">
              <option value="all" <?= $field==='all'?'selected':'' ?>>Todos</option>
              <option value="book" <?= $field==='book'?'selected':'' ?>>Libro</option>
              <option value="user" <?= $field==='user'?'selected':'' ?>>Usuario</option>
              <option value="id" <?= $field==='id'?'selected':'' ?>>Cédula</option>
              <option value="key" <?= $field==='key'?'selected':'' ?>>Llave</option>
            </select>
          </div>
          <div class="col-md-4 mb-2">
            <button class="btn btn-outline-primary mr-2" type="submit">Aplicar</button>
            <a class="btn btn-outline-secondary" href="returns.php">Limpiar</a>
          </div>
        </div>
      </form>
    </div>
  </div>

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
