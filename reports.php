<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Loan;
use App\Models\Book;
use App\Models\User;

AuthMiddleware::require();
Session::start();

$loanModel = new Loan();
$bookModel = new Book();

$export = $_GET['export'] ?? '';
if ($export) {
  $filename = $export . '_export_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=' . $filename);
  $out = fopen('php://output', 'w');
  $rows = [];
  if ($export === 'loans') {
    $rows = $loanModel->exportLoansData();
  } elseif ($export === 'overdue') {
    $rows = $loanModel->getOverdueLoans();
  } elseif ($export === 'users') {
    $userModel = new User();
    $rows = $userModel->all();
  } elseif ($export === 'books') {
    $rows = $bookModel->all();
  }
  if (!empty($rows)) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) { fputcsv($out, $r); }
  }
  fclose($out);
  exit;
}

$loanStats = $loanModel->getStatistics();
$bookStats = $bookModel->getStatistics();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reportes</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Reportes</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
      <a href="loans.php" class="btn btn-primary">Préstamos</a>
      <a href="returns.php" class="btn btn-secondary">Devoluciones</a>
      <a href="reports.php?export=loans" class="btn btn-success ml-2">Exportar préstamos (CSV)</a>
      <a href="reports.php?export=overdue" class="btn btn-warning ml-2">Exportar vencidos (CSV)</a>
      <a href="reports.php?export=users" class="btn btn-outline-primary ml-2">Exportar usuarios (CSV)</a>
      <a href="reports.php?export=books" class="btn btn-outline-secondary ml-2">Exportar libros (CSV)</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Resumen Libros</h5>
        <ul class="mb-0">
          <li>Títulos: <?= (int)$bookStats['total_titles'] ?></li>
          <li>Ejemplares: <?= (int)$bookStats['total_copies'] ?></li>
          <li>Disponibles: <?= (int)$bookStats['total_available'] ?></li>
          <li>En préstamo: <?= (int)($bookStats['total_copies'] - $bookStats['total_available']) ?></li>
        </ul>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Resumen Préstamos</h5>
        <ul class="mb-0">
          <li>Total: <?= (int)$loanStats['total'] ?></li>
          <li>Activos: <?= (int)$loanStats['active'] ?></li>
          <li>Devueltos: <?= (int)$loanStats['returned'] ?></li>
          <li>Vencidos: <?= (int)$loanStats['overdue'] ?></li>
          <li>Este mes: <?= (int)$loanStats['this_month'] ?></li>
          <li>Devoluciones este mes: <?= (int)$loanStats['returns_this_month'] ?></li>
        </ul>
      </div></div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Libros más prestados</h5>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead class="thead-light"><tr><th>Libro</th><th>Autor</th><th>Préstamos</th></tr></thead>
              <tbody>
                <?php foreach ($loanStats['most_borrowed'] as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['author'] ?? '') ?></td>
                    <td><?= (int)$row['loan_count'] ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($loanStats['most_borrowed'])): ?>
                  <tr><td colspan="3" class="text-muted text-center">Sin datos</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Usuarios más activos</h5>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead class="thead-light"><tr><th>Usuario</th><th>Préstamos</th></tr></thead>
              <tbody>
                <?php foreach ($loanStats['most_active_users'] as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?></td>
                    <td><?= (int)$row['loan_count'] ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($loanStats['most_active_users'])): ?>
                  <tr><td colspan="2" class="text-muted text-center">Sin datos</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
