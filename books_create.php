<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Book;

AuthMiddleware::require();
Session::start();

$bookModel = new Book();
$message = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        $copiesTotal = (int)($_POST['copies_total'] ?? 1);
        $data = [
            'isbn' => $_POST['isbn'] ?? null,
            'title' => $_POST['title'] ?? '',
            'author' => $_POST['author'] ?? '',
            'classification_id' => $_POST['classification_id'] ?? null,
            'classification_code' => $_POST['classification_code'] ?? null,
            'copies_total' => $copiesTotal,
            'origin_id' => isset($_POST['origin_id']) ? (int)$_POST['origin_id'] : null,
            'copies_available' => $copiesTotal,
            'label_id' => ($_POST['label_id'] ?? '') === '' ? null : (int)$_POST['label_id'],
            'library_id' => 683070001001,
            'room_id' => isset($_POST['room_id']) ? (int)$_POST['room_id'] : null,
            'notes' => $_POST['notes'] ?? null,
        ];
        $errors = $bookModel->validate($data);
        if (empty($errors)) {
            $created = $bookModel->createBook($data);
            if ($created) {
                $success = true;
                $message = 'Libro creado.';
            } else {
                $message = 'No se pudo crear el libro.';
            }
        } else {
            $message = 'Corrige los errores.';
        }
    }
}

$classifications = $bookModel->getClassifications();
$origins = $bookModel->getOrigins();
$labels = $bookModel->getLabels();
$rooms = $bookModel->getRooms();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nuevo Libro</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Nuevo Libro</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books.php" class="btn btn-outline-secondary">Volver</a>
    </div>
  </div>
  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <?= Session::csrfField() ?>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>ISBN (optional)</label>
            <input name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" />
          </div>
          <div class="form-group col-md-4">
            <label>Classification code (optional)</label>
            <input name="classification_code" class="form-control" value="<?= htmlspecialchars($_POST['classification_code'] ?? '') ?>" />
          </div>
          <div class="form-group col-md-4">
            <label>Label</label>
            <select name="label_id" class="form-control">
              <option value="">Sin etiqueta</option>
              <?php foreach ($labels as $l): ?>
                <option value="<?= (int)$l['id'] ?>"><?= htmlspecialchars($l['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Title</label>
            <input required name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" />
          </div>
          <div class="form-group col-md-6">
            <label>Author</label>
            <input required name="author" class="form-control" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Classification</label>
            <select required name="classification_id" class="form-control">
              <?php foreach ($classifications as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Origin</label>
            <select required name="origin_id" class="form-control">
              <?php foreach ($origins as $o): ?>
                <option value="<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Room</label>
            <select required name="room_id" class="form-control">
              <?php foreach ($rooms as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Copies</label>
            <input required type="number" min="1" name="copies_total" class="form-control" value="<?= htmlspecialchars($_POST['copies_total'] ?? '1') ?>" />
          </div>
          <div class="form-group col-md-9">
            <label>Notes</label>
            <input name="notes" class="form-control" value="<?= htmlspecialchars($_POST['notes'] ?? '') ?>" />
          </div>
        </div>
        <button class="btn btn-success" type="submit">Crear</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
