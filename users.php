<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\User;
use App\Models\Loan;

AuthMiddleware::require();
Session::start();

$userModel = new User();
$loanModel = new Loan();

$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';
$order = $_GET['order'] ?? 'DESC';

$users = $q !== '' ? $userModel->search($q, $field) : $userModel->getAllOrdered($order);

// Index active loans by user
$activeLoansByUser = [];
$activeLoans = $loanModel->getActiveLoans();
foreach ($activeLoans as $l) {
    $uid = (int)$l['id_number'];
    if (!isset($activeLoansByUser[$uid])) { $activeLoansByUser[$uid] = []; }
    $activeLoansByUser[$uid][] = $l;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Usuarios</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Usuarios (<?= count($users) ?>)</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
      <a href="loans.php" class="btn btn-primary">Préstamos</a>
      <a href="returns.php" class="btn btn-secondary">Devoluciones</a>
    </div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <h5 class="card-title">Buscar usuarios</h5>
    <form method="get" class="form-inline">
      <div class="form-row align-items-end w-100">
        <div class="col-md-4 mb-2">
          <label class="small text-muted d-block">Búsqueda</label>
          <input class="form-control" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nombre, apellido, correo, cédula, llave">
        </div>
        <div class="col-md-3 mb-2">
          <label class="small text-muted d-block">Campo</label>
          <select class="form-control" name="field">
            <option value="all" <?= $field==='all'?'selected':'' ?>>Todos</option>
            <option value="first_name" <?= $field==='first_name'?'selected':'' ?>>Nombre</option>
            <option value="last_name" <?= $field==='last_name'?'selected':'' ?>>Apellido</option>
            <option value="email" <?= $field==='email'?'selected':'' ?>>Correo</option>
            <option value="id" <?= $field==='id'?'selected':'' ?>>Cédula</option>
            <option value="key" <?= $field==='key'?'selected':'' ?>>Llave</option>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label class="small text-muted d-block">Orden</label>
          <select class="form-control" name="order">
            <option value="DESC" <?= strtoupper($order)==='DESC'?'selected':'' ?>>Más recientes</option>
            <option value="ASC" <?= strtoupper($order)==='ASC'?'selected':'' ?>>Más antiguos</option>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <button class="btn btn-outline-primary mr-2" type="submit">Aplicar</button>
          <a class="btn btn-outline-secondary" href="users.php">Limpiar</a>
        </div>
      </div>
    </form>
  </div></div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Llave</th>
            <th>Préstamos activos</th>
            <th>Días restantes (mín)</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): $uid=(int)$u['id_number']; $loans=$activeLoansByUser[$uid] ?? []; ?>
          <tr>
            <td><?= htmlspecialchars((string)$u['id_number']) ?></td>
            <td><?= htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?></td>
            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
            <td><?= htmlspecialchars((string)$u['user_key']) ?></td>
            <td><?= count($loans) ?></td>
            <td>
              <?php if (empty($loans)): ?>-
              <?php else: ?>
                <?php 
                  $minDays = null;
                  foreach ($loans as $l) {
                    $days = (int)floor((strtotime($l['due_at']) - time())/86400);
                    $minDays = $minDays === null ? $days : min($minDays, $days);
                  }
                  echo htmlspecialchars((string)$minDays);
                ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
