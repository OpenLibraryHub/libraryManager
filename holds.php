<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Hold;
use App\Models\Book;

AuthMiddleware::require();
Session::start();

$holdModel = new Hold();
$bookModel = new Book();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $message = 'Token inválido';
  } else {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($bookId && $userId) {
      if ($holdModel->userHasHold($bookId, $userId)) {
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

$books = $bookModel->all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de espera</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Lista de espera</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Libros</a>
    </div>
  </div>
  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <div class="card"><div class="card-body">
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
</body>
</html>


