<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Hold;
use App\Models\Book;

AuthMiddleware::require();
Session::start();

// Restrict holds page to admins only
if (!AuthMiddleware::hasRole('admin')) {
  Session::flash('error', 'Acceso restringido a administradores.');
  header('Location: dashboard.php');
  exit;
}

$holdModel = new Hold();
$bookModel = new Book();

$message = '';
$success = false;
$selectedBook = (int)($_GET['book_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $message = 'Token inválido';
  } else {
    // Cancel hold
    if (isset($_POST['cancel_hold'], $_POST['hold_id'])) {
      $hid = (int)$_POST['hold_id'];
      if ($hid > 0) {
        if ((new Hold())->cancelHold($hid)) {
          $success = true;
          $message = 'Solicitud cancelada.';
        } else {
          $message = 'No se pudo cancelar la solicitud.';
        }
      }
    } elseif (isset($_POST['fulfill_hold'], $_POST['hold_id'])) {
      // Fulfill now: create loan immediately
      $hid = (int)$_POST['hold_id'];
      $hold = (new Hold())->find($hid);
      if ($hold) {
        try {
          $ok = (new \App\Models\Loan())->createLoan((int)$hold['book_id'], (int)$hold['user_id'], 'Cumplido desde lista de espera', 15);
          if ($ok) {
            (new Hold())->markFulfilled($hid);
            $success = true;
            $message = 'Cumplida y préstamo creado.';
          } else {
            $message = 'No se pudo crear el préstamo.';
          }
        } catch (\Exception $e) {
          $message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
      }
    } else {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($bookId && $userId) {
      $book = $bookModel->find($bookId);
      if (!$book) {
        $message = 'Libro no encontrado.';
      } elseif ((int)($book['copies_total'] ?? 0) === 0 || \App\Models\Book::isArchivedRow($book)) {
        $message = 'El libro está archivado/eliminado y no admite lista de espera.';
      } elseif ((int)($book['copies_available'] ?? 0) > 0) {
        $message = 'Hay ejemplares disponibles. Realiza el préstamo desde la sección Préstamos.';
      } elseif ($holdModel->userHasHold($bookId, $userId)) {
        $message = 'El usuario ya está en lista de espera para este libro.';
      } else {
        if ($holdModel->createHold($bookId, $userId)) {
          $success = true;
          $message = 'Solicitud creada.';
        } else {
          $message = 'No se pudo crear la solicitud.';
        }
      }
    } else {
      $message = 'Datos inválidos';
    }
    }
  }
}

$books = $bookModel->getWaitlistEligibleBooks();
$queued = $holdModel->listHolds('queued', $selectedBook ?: null);
$fulfilled = $holdModel->listHolds('fulfilled');
$canceled = $holdModel->listHolds('canceled');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de espera</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
  <style>
    html, body { overflow-x: hidden; }
    .table-responsive { overflow-x: auto; }
    .table td, .table th { white-space: normal; word-break: break-word; }
    /* Match width of all sections */
    .narrow-card { max-width: 860px; margin-left: auto; margin-right: auto; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="position-relative mb-3 narrow-card">
    <h3 class="mb-0">Lista de espera</h3>
    <div class="position-absolute" style="right:0; top:0;">
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
    </div>
  </div>
  <form method="get" class="mb-3 narrow-card">
    <div class="form-row">
      <div class="col-md-6">
        <label class="small text-muted d-block">Filtrar por libro</label>
        <select class="form-control" name="book_id" onchange="this.form.submit()">
          <option value="0">Todos</option>
          <?php foreach ($books as $b): $sel = $selectedBook===(int)$b['id']?'selected':''; ?>
            <option value="<?= (int)$b['id'] ?>" <?= $sel ?>><?= htmlspecialchars(($b['title'] ?? '') . ' - ' . ($b['author'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </form>
  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?> narrow-card"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <div class="card narrow-card mx-auto"><div class="card-body">
    <h5 class="card-title">Crear solicitud</h5>
    <form method="post">
      <?= Session::csrfField() ?>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Libro</label>
          <select class="form-control" name="book_id" required>
            <?php foreach ($books as $b): ?>
              <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars(($b['title'] ?? '') . ' - ' . ($b['author'] ?? '')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label>Cédula usuario</label>
          <input type="number" class="form-control" name="user_id" required />
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Agregar a lista</button>
    </form>
  </div></div>
</div>

<div class="container py-0">
<div class="card mt-3 narrow-card mx-auto"><div class="card-body">
  <h5 class="card-title">Solicitudes en espera</h5>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead class="thead-light"><tr><th>Libro</th><th>Usuario</th><th>Cédula</th><th>Fecha</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($queued as $h): ?>
          <tr>
            <td><?= htmlspecialchars(($h['title'] ?? '') . ' - ' . ($h['author'] ?? '')) ?></td>
            <td><?= htmlspecialchars(($h['first_name'] ?? '') . ' ' . ($h['last_name'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)$h['id_number']) ?></td>
            <td><?= htmlspecialchars($h['created_at'] ?? '') ?></td>
            <td class="text-nowrap">
              <form method="post" class="d-inline" onsubmit="return confirm('¿Cumplir y crear préstamo?')">
                <?= Session::csrfField() ?>
                <input type="hidden" name="fulfill_hold" value="1" />
                <input type="hidden" name="hold_id" value="<?= (int)$h['id'] ?>" />
                <button class="btn btn-sm btn-success" type="submit">Cumplir</button>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('¿Cancelar solicitud?')">
                <?= Session::csrfField() ?>
                <input type="hidden" name="cancel_hold" value="1" />
                <input type="hidden" name="hold_id" value="<?= (int)$h['id'] ?>" />
                <button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($queued)): ?>
          <tr><td colspan="5" class="text-center text-muted">Sin solicitudes</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

<div class="card mt-3 narrow-card mx-auto"><div class="card-body">
  <h6 class="card-title">Cumplidas</h6>
  <ul class="mb-0">
    <?php foreach ($fulfilled as $h): ?>
      <li><?= htmlspecialchars(($h['title'] ?? '') . ' - ' . ($h['first_name'] ?? '') . ' ' . ($h['last_name'] ?? '')) ?> (<?= htmlspecialchars($h['fulfilled_at'] ?? '') ?>)</li>
    <?php endforeach; ?>
    <?php if (empty($fulfilled)): ?><li class="text-muted">Sin datos</li><?php endif; ?>
  </ul>
</div></div>

<div class="card mt-3 narrow-card mx-auto"><div class="card-body">
  <h6 class="card-title">Canceladas</h6>
  <ul class="mb-0">
    <?php foreach ($canceled as $h): ?>
      <li><?= htmlspecialchars(($h['title'] ?? '') . ' - ' . ($h['first_name'] ?? '') . ' ' . ($h['last_name'] ?? '')) ?> (<?= htmlspecialchars($h['canceled_at'] ?? '') ?>)</li>
    <?php endforeach; ?>
    <?php if (empty($canceled)): ?><li class="text-muted">Sin datos</li><?php endif; ?>
  </ul>
</div></div>

</div>

<script>
// simple toast
document.addEventListener('DOMContentLoaded', function(){
  const alerts = document.querySelectorAll('.alert');
  setTimeout(()=>alerts.forEach(a=>a.classList.add('show')), 50);
  setTimeout(()=>alerts.forEach(a=>a.remove()), 4000);
});
</script>

</body>
</html>


