<?php
require_once __DIR__ . '/config/autoload.php';

use App\Helpers\Session;
use App\Models\Loan;
use App\Models\Hold;

Session::start();
$userId = (int)Session::get('user_id', 0);
if ($userId <= 0) { header('Location: login.php'); exit; }

$loanModel = new Loan();
$holdModel = new Hold();

$loans = $loanModel->getUserActiveLoans($userId);
$history = $loanModel->getUserLoanHistory($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_hold'])) {
  if (Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $hid = (int)($_POST['hold_id'] ?? 0);
    if ($hid > 0) { $holdModel->cancelHold($hid); }
  }
  header('Location: account.php');
  exit;
}

// Simple holds list for user
$myHolds = [];
foreach ($holdModel->listHolds('queued') as $h) {
  if ((int)$h['user_id'] === $userId) { $myHolds[] = $h; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi cuenta</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Mi cuenta</h3>
    <a class="btn btn-outline-secondary" href="catalog.php">Catálogo</a>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Préstamos activos</h5>
        <ul class="mb-0">
          <?php foreach ($loans as $l): ?>
            <li><?= htmlspecialchars(($l['Titulo'] ?? $l['title'] ?? '') . ' - ' . ($l['Autor'] ?? $l['author'] ?? '')) ?> (vence <?= htmlspecialchars($l['fecha_limite'] ?? $l['due_at'] ?? '') ?>)</li>
          <?php endforeach; ?>
          <?php if (empty($loans)): ?><li class="text-muted">Sin préstamos</li><?php endif; ?>
        </ul>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Mis solicitudes</h5>
        <ul class="mb-0">
          <?php foreach ($myHolds as $h): ?>
            <li>
              <?= htmlspecialchars(($h['title'] ?? '') . ' - ' . ($h['author'] ?? '')) ?>
              <form method="post" class="d-inline" onsubmit="return confirm('¿Cancelar solicitud?')">
                <?= Session::csrfField() ?>
                <input type="hidden" name="cancel_hold" value="1" />
                <input type="hidden" name="hold_id" value="<?= (int)$h['id'] ?>" />
                <button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
              </form>
            </li>
          <?php endforeach; ?>
          <?php if (empty($myHolds)): ?><li class="text-muted">Sin solicitudes</li><?php endif; ?>
        </ul>
      </div></div>
    </div>
  </div>
</div>
</body>
</html>


