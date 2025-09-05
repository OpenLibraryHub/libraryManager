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
$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';
$activeOnly = ($_GET['active'] ?? '1') === '1';
// Here the loan is created
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $days = max(1, (int)($_POST['days'] ?? 15));
        $obs = trim((string)($_POST['observation'] ?? ''));
        try {
            // Resolve book: try by internal ID first; if not found, try by ISBN (even if numeric)
            $bookInput = trim((string)($_POST['book_id'] ?? ''));
            $bookRepo = new \App\Models\Book();
            $byId = $bookId > 0 ? $bookRepo->find($bookId) : null;
            if (!$byId && $bookInput !== '') {
                $byIsbn = $bookRepo->findByISBN($bookInput);
                if ($byIsbn) { $bookId = (int)$byIsbn['id']; }
            }

            // Resolve user: try by id_number; if not found, try by user_key (identifier)
            $userInput = trim((string)($_POST['user_id'] ?? ''));
            $userRepo = new \App\Models\User();
            $byUserId = $userId > 0 ? $userRepo->find($userId) : null;
            if (!$byUserId && $userInput !== '') {
                $byAny = $userRepo->findByIdentifier($userInput);
                if ($byAny) { $userId = (int)$byAny['id_number']; }
            }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'extend') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $loanId = (int)($_POST['loan_id'] ?? 0);
        $days = max(1, (int)($_POST['days'] ?? 5));
        if ($loanModel->extendLoan($loanId, $days)) {
            $success = true;
            $message = 'Préstamo extendido.';
        } else {
            $message = 'No se pudo extender (verifique que no esté vencido o devuelto).';
        }
    }
}

$activeLoans = $q !== '' ? $loanModel->searchLoans($q, $field, $activeOnly) : ($activeOnly ? $loanModel->getActiveLoans() : $loanModel->getReturnedLoans());
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
      <form method="post" id="createLoanForm">
        <?= Session::csrfField() ?>
        <input type="hidden" name="action" value="create" />
        <input type="hidden" name="confirm" id="confirmLoanConfirmed" value="0" />
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Libro (ID o ISBN)</label>
            <input required type="text" name="book_id" class="form-control" placeholder="ID interno o ISBN" list="books-ls" />
            <datalist id="books-ls"></datalist>
          </div>
          <div class="form-group col-md-3">
            <label>Usuario (Cédula o Llave)</label>
            <input required type="text" name="user_id" class="form-control" placeholder="Cédula o llave" list="users-ls" />
            <datalist id="users-ls"></datalist>
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

  <div class="modal fade" id="confirmLoanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar préstamo</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="mb-1 text-muted small">¿Los datos son correctos?</p>
          <p class="mb-1"><strong>Libro:</strong> <span id="confirmLoanBook"></span></p>
          <p class="mb-1"><strong>Usuario:</strong> <span id="confirmLoanUser"></span></p>
          <p class="mb-1"><strong>Días:</strong> <span id="confirmLoanDays"></span></p>
          <p class="mb-0"><strong>Observación:</strong> <span id="confirmLoanObs"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" id="confirmLoanYes">Sí, confirmar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Buscar préstamos</h5>
      <form method="get" class="form-inline">
        <div class="form-row align-items-end w-100">
          <div class="col-md-4 mb-2">
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
          <div class="col-md-3 mb-2">
            <label class="small text-muted d-block">Estado</label>
            <select class="form-control" name="active">
              <option value="1" <?= $activeOnly?'selected':'' ?>>Activos</option>
              <option value="0" <?= !$activeOnly?'selected':'' ?>>Devueltos</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <button class="btn btn-outline-primary mr-2" type="submit">Aplicar</button>
            <a class="btn btn-outline-secondary" href="loans.php">Limpiar</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title"><?= $activeOnly ? 'Préstamos activos' : 'Préstamos devueltos' ?></h5>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="thead-light">
            <tr>
              <th>ID</th><th>Libro</th><th>Autor</th><th>Usuario</th><th>Cédula</th><th>Fecha préstamo</th><th>Fecha límite</th><?php if(!$activeOnly): ?><th>Fecha entregado</th><?php endif; ?><?php if($activeOnly): ?><th>Estado</th><th>Acciones</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($activeLoans as $l): ?>
            <tr>
              <td><?= (int)($l['PrestamosID'] ?? $l['loan_id'] ?? 0) ?></td>
              <td><?= htmlspecialchars($l['Titulo'] ?? $l['title'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['Autor'] ?? $l['author'] ?? '') ?></td>
              <td><?= htmlspecialchars(($l['Nombre'] ?? $l['first_name'] ?? '') . ' ' . ($l['Apellido'] ?? $l['last_name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($l['Cedula'] ?? $l['id_number'] ?? '')) ?></td>
              <td><?= htmlspecialchars($l['fecha_prestamo'] ?? $l['loaned_at'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['fecha_limite'] ?? $l['due_at'] ?? '') ?></td>
              <?php if(!$activeOnly): ?><td><?= htmlspecialchars($l['fecha_entregado'] ?? $l['returned_at'] ?? '') ?></td><?php endif; ?>
              <?php if($activeOnly): ?>
                <td>
                  <?php 
                    $limite = $l['fecha_limite'] ?? $l['due_at'] ?? '';
                    $isOverdue = $limite && strtotime($limite) < time();
                    if ($isOverdue):
                      $daysOver = (int)floor((time() - strtotime($limite))/86400);
                  ?>
                      <span class="badge badge-danger">Vencido <?= $daysOver ?> d</span>
                  <?php else: ?>
                      <span class="badge badge-success">En curso</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="post" class="form-inline extend-form" data-loan-title="<?= htmlspecialchars($l['Titulo'] ?? $l['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?= Session::csrfField() ?>
                    <input type="hidden" name="action" value="extend" />
                    <input type="hidden" name="loan_id" value="<?= (int)($l['PrestamosID'] ?? $l['loan_id'] ?? 0) ?>" />
                    <div class="input-group input-group-sm">
                      <input type="number" class="form-control" name="days" value="5" min="1" style="min-width:110px">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Extender</button>
                      </div>
                    </div>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($activeLoans)): ?>
            <tr><td colspan="<?= $activeOnly?9:8 ?>" class="text-center text-muted">Sin préstamos</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="confirmExtendModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar extensión</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>¿Seguro que quieres extender el préstamo del libro <strong><span id="extendTitle"></span></strong> por <strong><span id="extendDays"></span></strong> días?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" id="confirmExtendYes">Sí, extender</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function fetchJSON(url) {
  const res = await fetch(url, {credentials: 'same-origin'});
  if (!res.ok) return {data: []};
  return res.json();
}
document.addEventListener('input', async (e) => {
  if (e.target.name === 'book_id') {
    const q = e.target.value.trim();
    if (q.length < 2) return;
    const {data} = await fetchJSON('api/books_lookup.php?q=' + encodeURIComponent(q));
    const dl = document.getElementById('books-ls');
    dl.innerHTML = '';
    data.forEach(b => {
      const opt = document.createElement('option');
      opt.value = b.isbn || String(b.id);
      opt.label = `${b.title} - ${b.author} (${b.isbn || 'ID ' + b.id})`;
      dl.appendChild(opt);
    });
  }
  if (e.target.name === 'user_id') {
    const q = e.target.value.trim();
    if (q.length < 2) return;
    const {data} = await fetchJSON('api/users_lookup.php?q=' + encodeURIComponent(q));
    const dl = document.getElementById('users-ls');
    dl.innerHTML = '';
    data.forEach(u => {
      const opt = document.createElement('option');
      opt.value = u.user_key || String(u.id_number);
      opt.label = `${u.first_name} ${u.last_name} (${u.id_number})`;
      dl.appendChild(opt);
    });
  }
});
document.addEventListener('DOMContentLoaded', function(){
  var form = document.getElementById('createLoanForm');
  var confirmedInput = document.getElementById('confirmLoanConfirmed');
  var modalEl = document.getElementById('confirmLoanModal');
  var bookSpan = document.getElementById('confirmLoanBook');
  var userSpan = document.getElementById('confirmLoanUser');
  var daysSpan = document.getElementById('confirmLoanDays');
  var obsSpan = document.getElementById('confirmLoanObs');
  var yesBtn = document.getElementById('confirmLoanYes');

  if (form) {
    form.addEventListener('submit', function(e){
      if (confirmedInput && confirmedInput.value === '1') { return; }
      e.preventDefault();
      var bookVal = (form.querySelector('input[name="book_id"]').value || '').trim();
      var userVal = (form.querySelector('input[name="user_id"]').value || '').trim();
      var daysVal = (form.querySelector('input[name="days"]').value || '').trim();
      var obsVal = (form.querySelector('input[name="observation"]').value || '').trim();
      if (bookSpan) bookSpan.textContent = bookVal || '';
      if (userSpan) userSpan.textContent = userVal || '';
      if (daysSpan) daysSpan.textContent = daysVal || '';
      if (obsSpan) obsSpan.textContent = obsVal || '-';
      if (window.jQuery && jQuery.fn.modal) {
        jQuery('#confirmLoanModal').modal('show');
      } else if (typeof bootstrap !== 'undefined') {
        var bs = new bootstrap.Modal(modalEl);
        bs.show();
      } else {
        if (confirm('¿Confirmar préstamo?')) {
          if (confirmedInput) confirmedInput.value = '1';
          form.submit();
        }
      }
    });
  }

  if (yesBtn && form) {
    yesBtn.addEventListener('click', function(){
      if (confirmedInput) confirmedInput.value = '1';
      form.submit();
    });
  }

  // Confirmación para extender préstamo
  var extendModal = document.getElementById('confirmExtendModal');
  var extendTitleSpan = document.getElementById('extendTitle');
  var extendDaysSpan = document.getElementById('extendDays');
  var extendYesBtn = document.getElementById('confirmExtendYes');
  var formToSubmit = null;

  var extendForms = document.querySelectorAll('form.extend-form');
  extendForms.forEach(function(f){
    f.addEventListener('submit', function(e){
      if (f.dataset.confirmed === '1') { return; }
      e.preventDefault();
      var dInput = f.querySelector('input[name="days"]');
      var days = (dInput && dInput.value) ? dInput.value.trim() : '';
      var title = f.getAttribute('data-loan-title') || '';
      if (extendTitleSpan) extendTitleSpan.textContent = title;
      if (extendDaysSpan) extendDaysSpan.textContent = days || '0';
      formToSubmit = f;
      if (window.jQuery && jQuery.fn.modal) {
        jQuery('#confirmExtendModal').modal('show');
      } else if (typeof bootstrap !== 'undefined') {
        var bs2 = new bootstrap.Modal(extendModal);
        bs2.show();
      } else {
        if (confirm('¿Seguro que quieres extender el préstamo por ' + (days || '0') + ' días?')) {
          f.dataset.confirmed = '1';
          f.submit();
        }
      }
    });
  });

  if (extendYesBtn) {
    extendYesBtn.addEventListener('click', function(){
      if (formToSubmit) {
        formToSubmit.dataset.confirmed = '1';
        formToSubmit.submit();
      }
    });
  }
});
</script>
</body>
</html>
