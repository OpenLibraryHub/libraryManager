<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\User;
use App\Models\Loan;
use App\Controllers\UserController;

AuthMiddleware::require();
Session::start();

$userModel = new User();
$loanModel = new Loan();

// Create user handling
$createMessage = '';
$createSuccess = false;
$createErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $createMessage = 'Token inválido';
  } else {
    // Extra server-side guards: optional fields (email, phone, address); numbers must be non-negative
    $payload = $_POST;
    $localErrors = [];
    $idVal = isset($payload['id_number']) ? (int)$payload['id_number'] : null;
    $keyVal = isset($payload['user_key']) && $payload['user_key'] !== '' ? (int)$payload['user_key'] : null;
    $phoneVal = ($payload['phone'] ?? '') !== '' ? (int)$payload['phone'] : null;
    if ($idVal === null || $idVal < 1) { $localErrors['id_number'][] = 'Debe ser un número positivo.'; }
    if ($keyVal !== null && $keyVal < 1) { $localErrors['user_key'][] = 'Debe ser un número positivo.'; }
    // Length and leading-zero constraints (UI-level)
    $idDigits = preg_replace('/\D/', '', (string)($payload['id_number'] ?? ''));
    if ($idDigits === '' || strlen($idDigits) <= 8) { $localErrors['id_number'][] = 'Debe tener más de 8 dígitos.'; }
    if ($idDigits !== '' && $idDigits[0] === '0') { $localErrors['id_number'][] = 'No puede iniciar con 0.'; }
    $keyDigits = preg_replace('/\D/', '', (string)($payload['user_key'] ?? ''));
    if ($keyDigits !== '' && strlen($keyDigits) !== 12) { $localErrors['user_key'][] = 'Debe tener 12 dígitos.'; }
    if ($keyDigits !== '' && $keyDigits[0] === '0') { $localErrors['user_key'][] = 'No puede iniciar con 0.'; }
    if ($phoneVal !== null && $phoneVal < 0) { $localErrors['phone'][] = 'No puede ser negativo.'; }
    if (trim((string)($payload['first_name'] ?? '')) === '') { $localErrors['first_name'][] = 'Requerido.'; }
    if (trim((string)($payload['last_name'] ?? '')) === '') { $localErrors['last_name'][] = 'Requerido.'; }

    if (!empty($localErrors)) {
      $createMessage = 'Revise el formulario.';
      $createErrors = $localErrors;
    } else {
      $controller = new UserController();
      $res = $controller->create($_POST);
      $createMessage = $res['message'] ?? '';
      $createSuccess = (bool)($res['success'] ?? false);
      $createErrors = $res['errors'] ?? [];
    }
  }
}

$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';
$order = $_GET['order'] ?? 'DESC';
$sortBy = $_GET['sort'] ?? 'created_at';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

if ($q !== '') {
  $all = $userModel->search($q, $field);
  $total = count($all);
  $users = array_slice($all, $offset, $perPage);
} else {
  // Quick count
  $total = $userModel->count();
  $users = $userModel->getPageOrdered($perPage, $offset, $order, $sortBy);
}
$pages = max(1, (int)ceil($total / $perPage));

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

  <?php if ($createMessage): ?>
    <div class="alert <?= $createSuccess ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($createMessage) ?></div>
  <?php endif; ?>

  <div class="card mb-3"><div class="card-body">
    <h5 class="card-title">Crear usuario</h5>
    <form method="post">
      <?= Session::csrfField() ?>
      <input type="hidden" name="action" value="create" />
      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Cédula (requerido)</label>
          <input type="number" name="id_number" min="1" inputmode="numeric" pattern="[0-9]*" class="form-control <?= isset($createErrors['id_number']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required />
          <?php if (isset($createErrors['id_number'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['id_number'][0]) ?></div><?php endif; ?>
        </div>
        <div class="form-group col-md-3">
          <label>Llave</label>
          <input type="number" name="user_key" min="1" inputmode="numeric" pattern="[0-9]{12}" class="form-control <?= isset($createErrors['user_key']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['user_key'] ?? '') ?>" />
          <?php if (isset($createErrors['user_key'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['user_key'][0]) ?></div><?php endif; ?>
        </div>
        <div class="form-group col-md-3">
          <label>Nombre (requerido)</label>
          <input name="first_name" class="form-control <?= isset($createErrors['first_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required />
          <?php if (isset($createErrors['first_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['first_name'][0]) ?></div><?php endif; ?>
        </div>
        <div class="form-group col-md-3">
          <label>Apellido (requerido)</label>
          <input name="last_name" class="form-control <?= isset($createErrors['last_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required />
          <?php if (isset($createErrors['last_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['last_name'][0]) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Correo</label>
          <input type="email" name="email" class="form-control <?= isset($createErrors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
          <?php if (isset($createErrors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['email'][0]) ?></div><?php endif; ?>
        </div>
        <div class="form-group col-md-4">
          <label>Teléfono</label>
          <input type="number" name="phone" min="0" class="form-control <?= isset($createErrors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
          <?php if (isset($createErrors['phone'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['phone'][0]) ?></div><?php endif; ?>
        </div>
        <div class="form-group col-md-4">
          <label>Dirección</label>
          <input name="address" class="form-control <?= isset($createErrors['address']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" />
          <?php if (isset($createErrors['address'])): ?><div class="invalid-feedback"><?= htmlspecialchars($createErrors['address'][0]) ?></div><?php endif; ?>
        </div>
      </div>
      <button class="btn btn-success" type="submit">Crear usuario</button>
    </form>
  </div></div>

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
          <label class="small text-muted d-block">Ordenar por</label>
          <select class="form-control" name="sort">
            <option value="created_at" <?= $sortBy==='created_at'?'selected':'' ?>>Fecha</option>
            <option value="first_name" <?= $sortBy==='first_name'?'selected':'' ?>>Nombre</option>
            <option value="last_name" <?= $sortBy==='last_name'?'selected':'' ?>>Apellido</option>
            <option value="email" <?= $sortBy==='email'?'selected':'' ?>>Correo</option>
            <option value="id_number" <?= $sortBy==='id_number'?'selected':'' ?>>Cédula</option>
            <option value="user_key" <?= $sortBy==='user_key'?'selected':'' ?>>Llave</option>
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
  <nav aria-label="Paginación" class="mt-3">
    <ul class="pagination">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>&order=<?= htmlspecialchars($order) ?>&sort=<?= htmlspecialchars($sortBy) ?>">Anterior</a>
      </li>
      <?php for ($p = max(1,$page-2); $p <= min($pages, $page+2); $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $p ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>&order=<?= htmlspecialchars($order) ?>&sort=<?= htmlspecialchars($sortBy) ?>"><?= $p ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>&order=<?= htmlspecialchars($order) ?>&sort=<?= htmlspecialchars($sortBy) ?>">Siguiente</a>
      </li>
    </ul>
  </nav>
</div>
</body>
</html>
