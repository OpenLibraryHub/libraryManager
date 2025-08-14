<?php
require_once __DIR__ . '/config/autoload.php';

use App\Helpers\Session;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Hold;

Session::start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: catalog.php'); exit; }

$bookModel = new Book();
$loanModel = new Loan();
$holdModel = new Hold();

$book = $bookModel->getBookWithDetails($id);
if (!$book) { header('Location: catalog.php'); exit; }

$message = Session::flash('error') ?: Session::flash('success');

// Handle hold request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hold'])) {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::flash('error', 'Token de seguridad inválido.');
    header('Location: book.php?id=' . $id);
    exit;
  }
  if (!Session::isLoggedIn()) {
    Session::flash('error', 'Debe iniciar sesión para solicitar un libro.');
    header('Location: login.php');
    exit;
  }
  $userId = (int)Session::get('user_id', 0);
  // allow holds only if not available and not archived
  $eligible = $bookModel->getWaitlistEligibleBooks();
  $eligibleIds = array_column($eligible, 'id');
  if (!in_array($id, $eligibleIds, true)) {
    Session::flash('error', 'Este libro está disponible o no es elegible para lista de espera.');
  } else if ($holdModel->userHasHold($id, $userId)) {
    Session::flash('error', 'Ya estás en la lista de espera para este libro.');
  } else if ($loanModel->userHasActiveLoan($userId, $id)) {
    Session::flash('error', 'Ya tienes este libro prestado.');
  } else {
    if ($holdModel->createHold($id, $userId)) {
      Session::flash('success', 'Se agregó a la lista de espera.');
    } else {
      Session::flash('error', 'No se pudo agregar a la lista de espera.');
    }
  }
  header('Location: book.php?id=' . $id);
  exit;
}

$activeLoansCount = $loanModel->countActiveLoansByBook($id);
$queue = $holdModel->getQueueForBook($id);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($book['title'] ?? 'Libro') ?></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Información del libro</h3>
    <div>
      <a class="btn btn-outline-secondary" href="catalog.php">Regresar</a>
      <?php if (Session::isLoggedIn()): ?>
        <a class="btn btn-outline-secondary" href="account.php">Mi cuenta</a>
        <a class="btn btn-outline-danger" href="logout.php">Salir</a>
      <?php else: ?>
        <a class="btn btn-primary" href="login.php">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= h($message) ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <h4 class="mb-1"><?= h($book['title'] ?? '') ?></h4>
          <div class="text-muted mb-2">por <?= h($book['author'] ?? '') ?></div>
          <div><strong>ISBN:</strong> <?= h($book['isbn'] ?? '-') ?></div>
          <div><strong>Código:</strong> <?= h($book['classification_code'] ?? '-') ?></div>
          <div><strong>Clasificación:</strong> <?= h($book['classification_desc'] ?? '-') ?></div>
          <div><strong>Etiqueta:</strong> <?= h($book['label_desc'] ?? '-') ?> <?= isset($book['label_color'])? '('.h($book['label_color']).')':'' ?></div>
          <div><strong>Sala:</strong> <?= h($book['room_desc'] ?? '-') ?></div>
          <div class="mt-2"><strong>Notas:</strong> <?= h($book['notes'] ?? '-') ?></div>
        </div>
        <div class="col-md-4">
          <?php $avail = (int)($book['copies_available'] ?? 0); ?>
          <div><strong>Copias totales:</strong> <?= (int)($book['copies_total'] ?? 0) ?></div>
          <div><strong>Disponibles:</strong> <?= (int)$avail ?></div>
          <?php if (App\Models\Book::isArchivedRow($book)): ?>
            <div class="mt-2"><span class="badge badge-secondary">Archivado</span></div>
          <?php endif; ?>
          <div class="mt-3">
            <?php if ($avail <= 0 && !App\Models\Book::isArchivedRow($book)): ?>
              <form method="post">
                <?= Session::csrfField() ?>
                <input type="hidden" name="add_hold" value="1" />
                <button class="btn btn-primary btn-block" type="submit">Unirse a lista de espera</button>
              </form>
            <?php else: ?>
              <button class="btn btn-outline-secondary btn-block" disabled>Disponible para préstamo en biblioteca</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Préstamos activos</h5>
          <p class="mb-2 text-muted">Actualmente prestados: <?= (int)$activeLoansCount ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Lista de espera</h5>
          <?php if (!empty($queue)): ?>
            <ul class="mb-0">
              <?php foreach ($queue as $pos => $h): ?>
                <li>#<?= (int)($pos+1) ?>: <?= h(($h['first_name'] ?? '').' '.($h['last_name'] ?? '').' ('.$h['id_number'].')') ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-muted">Sin solicitudes</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>
</body>
</html>


