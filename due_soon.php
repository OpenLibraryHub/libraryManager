<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Loan;

AuthMiddleware::require();
Session::start();

$days = max(1, (int)($_GET['days'] ?? 3));
$loanModel = new Loan();
$dueSoon = $loanModel->getDueSoonLoans($days);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Préstamos por vencer</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Préstamos por vencer (<?= (int)$days ?> días)</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="loans.php" class="btn btn-outline-secondary">Préstamos</a>
      <a href="reports.php?export=due_soon&days=<?= (int)$days ?>" class="btn btn-warning ml-2">Exportar CSV</a>
    </div>
  </div>

  <form class="form-inline mb-3" method="get">
    <label class="mr-2">Días</label>
    <input type="number" class="form-control mr-2" name="days" value="<?= (int)$days ?>" min="1">
    <button class="btn btn-outline-primary" type="submit">Aplicar</button>
  </form>

  <div class="card"><div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Libro</th><th>Usuario</th><th>Cédula</th><th>Fecha límite</th><th>Días restantes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dueSoon as $r): ?>
            <tr>
              <td><?= htmlspecialchars(($r['title'] ?? '') . ' - ' . ($r['author'] ?? '')) ?></td>
              <td><?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($r['id_number'] ?? '')) ?></td>
              <td><?= htmlspecialchars($r['due_at'] ?? '') ?></td>
              <td><span class="badge badge-warning"><?= (int)($r['days_left'] ?? 0) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($dueSoon)): ?>
            <tr><td colspan="5" class="text-center text-muted">Sin resultados</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div></div>
</div>
</body>
</html>


