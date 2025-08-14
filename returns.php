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
$activeOnly = ($_GET['active'] ?? '1') === '1';

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

$loans = $q !== '' ? $loanModel->searchLoans($q, $field, $activeOnly) : ($activeOnly ? $loanModel->getActiveLoans() : $loanModel->getReturnedLoans());
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
      <h5 class="card-title">Buscar préstamos</h5>
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
          <div class="col-md-2 mb-2">
            <label class="small text-muted d-block">Estado</label>
            <select class="form-control" name="active">
              <option value="1" <?= $activeOnly?'selected':'' ?>>Activos</option>
              <option value="0" <?= !$activeOnly?'selected':'' ?>>Devueltos</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
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
            <?php if(!$activeOnly): ?><th>Fecha entregado</th><?php endif; ?>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $l): ?>
          <tr>
            <td><?= (int)($l['loan_id'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string)($l['title'] ?? '')) ?><?= ($l['author'] ?? '') !== '' ? ' - ' . htmlspecialchars((string)$l['author']) : '' ?></td>
            <td><?= htmlspecialchars(trim((string)($l['first_name'] ?? '') . ' ' . (string)($l['last_name'] ?? ''))) ?><?= isset($l['id_number']) ? ' (' . htmlspecialchars((string)$l['id_number']) . ')' : '' ?></td>
            <td><?= htmlspecialchars((string)($l['loaned_at'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($l['due_at'] ?? '')) ?></td>
            <?php if(!$activeOnly): ?><td><?= htmlspecialchars((string)($l['returned_at'] ?? '')) ?></td><?php endif; ?>
            <td>
              <?php if ((int)($l['returned'] ?? 0) === 0): ?>
                <form id="returnForm_<?= (int)($l['loan_id'] ?? 0) ?>" method="post" class="mb-0">
                  <?= Session::csrfField() ?>
                  <input type="hidden" name="action" value="return" />
                  <input type="hidden" name="loan_id" value="<?= (int)($l['loan_id'] ?? 0) ?>" />
                  <button type="button"
                          class="btn btn-sm btn-success open-return-modal"
                          data-loan-id="<?= (int)($l['loan_id'] ?? 0) ?>"
                          data-book="<?= htmlspecialchars((string)($l['title'] ?? '')) ?><?= ($l['author'] ?? '') !== '' ? ' — ' . htmlspecialchars((string)$l['author']) : '' ?>"
                          data-user="<?= htmlspecialchars(trim((string)($l['first_name'] ?? '') . ' ' . (string)($l['last_name'] ?? ''))) ?><?= isset($l['id_number']) ? ' (' . htmlspecialchars((string)$l['id_number']) . ')' : '' ?>">
                    Marcar devuelto
                  </button>
                </form>
              <?php else: ?>
                <span class="badge badge-secondary">Devuelto</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($loans)): ?>
          <tr><td colspan="<?= $activeOnly ? 6 : 7 ?>" class="text-center text-muted"><?= $activeOnly ? 'Sin préstamos activos' : 'Sin préstamos devueltos' ?></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
<div class="modal fade" id="confirmReturnModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar devolución</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-1 text-muted small">Libro</p>
        <div id="confirmReturnBook" class="font-weight-bold mb-2"></div>
        <p class="mb-1 text-muted small">Usuario</p>
        <div id="confirmReturnUser" class="mb-3"></div>
        <div class="alert alert-info mb-0">¿El libro fue entregado?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-success" id="confirmReturnYes">Sí</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var currentLoanId = null;
    var buttons = document.querySelectorAll('.open-return-modal');
    buttons.forEach(function(btn){
      btn.addEventListener('click', function(){
        currentLoanId = this.getAttribute('data-loan-id');
        document.getElementById('confirmReturnBook').textContent = this.getAttribute('data-book') || '';
        document.getElementById('confirmReturnUser').textContent = this.getAttribute('data-user') || '';
        if (window.jQuery && jQuery.fn.modal) {
          jQuery('#confirmReturnModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
          var el = document.getElementById('confirmReturnModal');
          if (el) { new bootstrap.Modal(el).show(); }
        }
      });
    });

    var yesBtn = document.getElementById('confirmReturnYes');
    if (yesBtn) {
      yesBtn.addEventListener('click', function(){
        if (!currentLoanId) return;
        var form = document.getElementById('returnForm_' + currentLoanId);
        if (form) form.submit();
      });
    }
  });
</script>
