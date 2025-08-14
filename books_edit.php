<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Book;

AuthMiddleware::require();
Session::start();

// Normal processing

$bookModel = new Book();
$id = (int)($_GET['id'] ?? 0);
$book = $id ? $bookModel->getBookWithDetails($id) : null;
if (!$book) {
  http_response_code(404);
  die('Libro no encontrado');
}

$message = '';
$success = false;

// Load reference lists before handling POST (used for validation/defaults)
$classifications = $bookModel->getClassifications();
$origins = $bookModel->getOrigins();
$labels = $bookModel->getLabels();
$rooms = $bookModel->getRooms();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $message = 'Token inválido';
  } else {
    // Compute default "absence" IDs using the first option from each list (string-safe)
    $defaultClassificationId = isset($classifications[0]['id']) ? (string)$classifications[0]['id'] : null;
    $defaultOriginId = isset($origins[0]['id']) ? (string)$origins[0]['id'] : null;
    $defaultLabelId = isset($labels[0]['id']) ? (string)$labels[0]['id'] : null;
    $defaultRoomId = isset($rooms[0]['id']) ? (string)$rooms[0]['id'] : null;

    $strOrDefault = function (string $key, ?string $default) {
      $raw = (string)($_POST[$key] ?? '');
      return $raw === '' ? $default : $raw;
    };

    $payload = [
      'id' => $id,
      'isbn' => ($_POST['isbn'] ?? '') === '' ? null : $_POST['isbn'],
      'title' => $_POST['title'] ?? '',
      'author' => ($_POST['author'] ?? '') === '' ? null : $_POST['author'],
      'classification_id' => $strOrDefault('classification_id', $defaultClassificationId),
      'classification_code' => ($_POST['classification_code'] ?? '') === '' ? null : $_POST['classification_code'],
      'origin_id' => $strOrDefault('origin_id', $defaultOriginId),
      'label_id' => $strOrDefault('label_id', $defaultLabelId),
      'room_id' => $strOrDefault('room_id', $defaultRoomId),
      'notes' => ($_POST['notes'] ?? '') === '' ? null : $_POST['notes'],
    ];

    // Validate FK values against allowed sets (string-safe). If not valid, fall back to defaults
    $validClassificationIds = array_map('strval', array_column($classifications, 'id'));
    if ($payload['classification_id'] === null || !in_array((string)$payload['classification_id'], $validClassificationIds, true)) {
      $payload['classification_id'] = $defaultClassificationId;
    }
    $validOriginIds = array_map('strval', array_column($origins, 'id'));
    if ($payload['origin_id'] === null || !in_array((string)$payload['origin_id'], $validOriginIds, true)) {
      $payload['origin_id'] = $defaultOriginId;
    }
    $validLabelIds = array_map('strval', array_column($labels, 'id'));
    if ($payload['label_id'] === null || !in_array((string)$payload['label_id'], $validLabelIds, true)) {
      $payload['label_id'] = $defaultLabelId;
    }
    $validRoomIds = array_map('strval', array_column($rooms, 'id'));
    if ($payload['room_id'] === null || !in_array((string)$payload['room_id'], $validRoomIds, true)) {
      $payload['room_id'] = $defaultRoomId;
    }
    // Basic validation reuse
    $errors = $bookModel->validate($payload, true);
    if (empty($errors)) {
      if ($bookModel->updateBook($id, $payload)) {
        $success = true;
        $message = 'Libro actualizado';
        $book = $bookModel->getBookWithDetails($id);
      } else {
        $message = 'Sin cambios o error al actualizar';
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
  <title>Editar Libro</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Editar Libro</h3>
    <div>
      <a href="books.php" class="btn btn-outline-secondary">Volver</a>
    </div>
  </div>
  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success':'alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <div class="card"><div class="card-body">
    <form method="post">
      <?= Session::csrfField() ?>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>ISBN</label>
          <input name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>" />
        </div>
        <div class="form-group col-md-4">
          <label>Código de clasificación</label>
          <input name="classification_code" class="form-control" value="<?= htmlspecialchars($book['classification_code'] ?? '') ?>" />
        </div>
        <div class="form-group col-md-4">
          <label>Etiqueta</label>
          <select name="label_id" class="form-control">
            <?php foreach ($labels as $l): $sel=(string)$l['id']===(string)($book['label_id']??'')?'selected':''; ?>
              <option value="<?= (int)$l['id'] ?>" <?= $sel ?>><?= htmlspecialchars($l['body']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Título</label>
          <input required name="title" class="form-control" value="<?= htmlspecialchars($book['title'] ?? '') ?>" />
        </div>
        <div class="form-group col-md-6">
          <label>Autor</label>
          <input required name="author" class="form-control" value="<?= htmlspecialchars($book['author'] ?? '') ?>" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Clasificación</label>
          <select name="classification_id" class="form-control">
            <?php foreach ($classifications as $c): $sel=(string)$c['id']===(string)($book['classification_id']??'')?'selected':''; ?>
              <option value="<?= htmlspecialchars($c['id']) ?>" <?= $sel ?>><?= htmlspecialchars($c['body']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label>Origen</label>
          <select name="origin_id" class="form-control">
            <?php foreach ($origins as $o): $sel=(string)$o['id']===(string)($book['origin_id']??'')?'selected':''; ?>
              <option value="<?= (int)$o['id'] ?>" <?= $sel ?>><?= htmlspecialchars($o['body']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label>Sala</label>
          <select name="room_id" class="form-control">
            <?php foreach ($rooms as $r): $sel=(string)$r['id']===(string)($book['room_id']??'')?'selected':''; ?>
              <option value="<?= (int)$r['id'] ?>" <?= $sel ?>><?= htmlspecialchars($r['body']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-12">
          <label>Observación</label>
          <input name="notes" class="form-control" value="<?= htmlspecialchars($book['notes'] ?? '') ?>" />
        </div>
      </div>
      <button class="btn btn-success" type="submit">Guardar</button>
    </form>
  </div></div>
</div>
</body>
</html>


