<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Book;
use App\Models\Loan;

AuthMiddleware::require();
Session::start();

$id = (int)($_GET['id'] ?? 0);
$bookModel = new Book();
$loanModel = new Loan();
$book = $bookModel->getBookWithDetails($id);
if (!$book) { http_response_code(404); die('Libro no encontrado'); }

$activeLoans = $loanModel->searchLoans($book['title'] ?? '', 'book', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detalle del libro</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Detalle del libro</h3>
    <div>
      <a href="books.php" class="btn btn-outline-secondary">Volver</a>
      <a href="books_edit.php?id=<?= (int)$book['id'] ?>" class="btn btn-primary">Editar</a>
    </div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5><?= htmlspecialchars($book['title'] ?? '') ?></h5>
        <p class="mb-1"><strong>Autor:</strong> <?= htmlspecialchars($book['author'] ?? '') ?></p>
        <p class="mb-1"><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?? '') ?></p>
        <p class="mb-1"><strong>Código de clasificación:</strong> <?= htmlspecialchars($book['classification_code'] ?? '') ?></p>
        <p class="mb-1"><strong>Clasificación:</strong> <?= htmlspecialchars($book['classification_desc'] ?? '') ?></p>
        <p class="mb-1"><strong>Origen:</strong> <?= htmlspecialchars($book['origin_desc'] ?? '') ?></p>
        <p class="mb-1"><strong>Etiqueta:</strong> <?= htmlspecialchars($book['label_desc'] ?? '') ?></p>
        <p class="mb-1"><strong>Sala:</strong> <?= htmlspecialchars($book['room_desc'] ?? '') ?></p>
      </div>
      <div class="col-md-6">
        <p class="mb-1"><strong>Disponibles:</strong> <?= (int)($book['copies_available'] ?? 0) ?> / <?= (int)($book['copies_total'] ?? 0) ?></p>
        <p class="mb-1"><strong>Observación:</strong> <?= htmlspecialchars($book['notes'] ?? '') ?></p>
      </div>
    </div>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Préstamos activos de este libro</h5>
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="thead-light">
          <tr><th>ID</th><th>Usuario</th><th>Cédula</th><th>Fecha préstamo</th><th>Fecha límite</th></tr>
        </thead>
        <tbody>
          <?php foreach ($activeLoans as $l): ?>
            <tr>
              <td><?= (int)($l['PrestamosID'] ?? $l['loan_id'] ?? 0) ?></td>
              <td><?= htmlspecialchars(($l['Nombre'] ?? $l['first_name'] ?? '') . ' ' . ($l['Apellido'] ?? $l['last_name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($l['Cedula'] ?? $l['id_number'] ?? '')) ?></td>
              <td><?= htmlspecialchars($l['fecha_prestamo'] ?? $l['loaned_at'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['fecha_limite'] ?? $l['due_at'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($activeLoans)): ?>
            <tr><td colspan="5" class="text-center text-muted">Sin préstamos activos</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div></div>
</div>
</body>
</html>


