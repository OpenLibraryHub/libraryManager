<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Book;
use App\Models\Loan;

AuthMiddleware::require();
if (!\App\Middleware\AuthMiddleware::hasRole('admin')) { http_response_code(403); die('No autorizado'); }
Session::start();

$id = (int)($_GET['id'] ?? 0);
$bookModel = new Book();
$loanModel = new Loan();
$message = '';
$success = false;

if (!$id) {
  http_response_code(400);
  die('ID inválido');
}

$book = $bookModel->find($id);
if (!$book) {
  http_response_code(404);
  die('Libro no encontrado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $message = 'Token inválido';
  } else {
    $active = $loanModel->countActiveLoansByBook($id);
    if ($active > 0) {
      $message = 'No se puede eliminar: hay préstamos activos para este libro.';
    } else {
      if ($bookModel->delete($id)) {
        $success = true;
        Session::flash('success', 'Libro eliminado');
        header('Location: books.php');
        exit;
      } else {
        $message = 'No se pudo eliminar el libro.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Eliminar libro</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Eliminar libro</h3>
    <a class="btn btn-outline-secondary" href="books.php">Volver</a>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card"><div class="card-body">
    <p>¿Está seguro de eliminar el libro <strong><?= htmlspecialchars($book['title'] ?? '') ?></strong> (ID <?= (int)$book['id'] ?>)? Esta acción no se puede deshacer.</p>
    <form method="post">
      <?= Session::csrfField() ?>
      <button class="btn btn-danger" type="submit">Eliminar</button>
      <a class="btn btn-secondary" href="books.php">Cancelar</a>
    </form>
  </div></div>
</div>
</body>
</html>


