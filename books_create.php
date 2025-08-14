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

// Load reference lists for defaults/validation
$classifications = $bookModel->getClassifications();
$origins = $bookModel->getOrigins();
$labels = $bookModel->getLabels();
$rooms = $bookModel->getRooms();

$existingIsbnBook = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token inválido';
    } else {
        // Defaults (string-safe like edit page)
        $defaultClassificationId = isset($classifications[0]['id']) ? (string)$classifications[0]['id'] : null;
        $defaultOriginId = isset($origins[0]['id']) ? (string)$origins[0]['id'] : null;
        $defaultLabelId = isset($labels[0]['id']) ? (string)$labels[0]['id'] : null;
        $defaultRoomId = isset($rooms[0]['id']) ? (string)$rooms[0]['id'] : null;

        $strOrDefault = function (string $key, ?string $default) {
            $raw = (string)($_POST[$key] ?? '');
            return $raw === '' ? $default : $raw;
        };

        $copiesTotal = (int)($_POST['copies_total'] ?? 1);
        if ($copiesTotal < 1) { $copiesTotal = 1; }

        $data = [
            'isbn' => ($_POST['isbn'] ?? '') === '' ? null : $_POST['isbn'],
            'title' => $_POST['title'] ?? '',
            'author' => ($_POST['author'] ?? '') === '' ? null : $_POST['author'],
            'classification_id' => $strOrDefault('classification_id', $defaultClassificationId),
            'classification_code' => ($_POST['classification_code'] ?? '') === '' ? null : $_POST['classification_code'],
            'copies_total' => $copiesTotal,
            'origin_id' => $strOrDefault('origin_id', $defaultOriginId),
            'copies_available' => $copiesTotal,
            'label_id' => $strOrDefault('label_id', $defaultLabelId),
            'library_id' => 683070001001,
            'room_id' => $strOrDefault('room_id', $defaultRoomId),
            'notes' => ($_POST['notes'] ?? '') === '' ? null : $_POST['notes'],
        ];

        // Validate FK values against allowed sets; fallback to defaults if invalid
        $validClassificationIds = array_map('strval', array_column($classifications, 'id'));
        if ($data['classification_id'] === null || !in_array((string)$data['classification_id'], $validClassificationIds, true)) {
            $data['classification_id'] = $defaultClassificationId;
        }
        $validOriginIds = array_map('strval', array_column($origins, 'id'));
        if ($data['origin_id'] === null || !in_array((string)$data['origin_id'], $validOriginIds, true)) {
            $data['origin_id'] = $defaultOriginId;
        }
        $validLabelIds = array_map('strval', array_column($labels, 'id'));
        if ($data['label_id'] === null || !in_array((string)$data['label_id'], $validLabelIds, true)) {
            $data['label_id'] = $defaultLabelId;
        }
        $validRoomIds = array_map('strval', array_column($rooms, 'id'));
        if ($data['room_id'] === null || !in_array((string)$data['room_id'], $validRoomIds, true)) {
            $data['room_id'] = $defaultRoomId;
        }

        // Pre-check ISBN existence to drive UX modal
        if (!empty($data['isbn'])) {
            $existingIsbnBook = $bookModel->findByISBN($data['isbn']);
        }

        $errors = $bookModel->validate($data);
        if (empty($errors) && !$existingIsbnBook) {
            $created = $bookModel->createBook($data);
            if ($created) {
                $success = true;
                $message = 'Libro creado.';
            } else {
                $message = 'No se pudo crear el libro.';
            }
        } else {
            if ($existingIsbnBook) {
                $message = 'Ese ISBN ya existe.';
            } else {
                $message = 'Corrige los errores.';
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
  <title>Nuevo Libro</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Verify ISBN via API and open modal
      var verifyBtn = document.getElementById('verifyIsbnBtn');
      if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
          var input = document.getElementById('isbnInput');
          var isbn = (input ? input.value.trim() : '');
          if (!isbn) { alert('Ingresa un ISBN para verificar.'); return; }
          fetch('api/books_check_isbn.php?isbn=' + encodeURIComponent(isbn), { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(data){
              if (data && data.found && data.book && data.book.id) {
                var link = document.getElementById('isbnModalEditLink');
                if (link) { link.href = 'books_edit.php?id=' + data.book.id; }
                var text = document.getElementById('isbnModalText');
                if (text) { text.textContent = 'Ese ISBN ya existe: ' + (data.book.title || '') + ' — ' + (data.book.author || ''); }
                if (window.jQuery && jQuery.fn.modal) {
                  jQuery('#isbnExistsModal').modal('show');
                } else if (typeof bootstrap !== 'undefined') {
                  var el = document.getElementById('isbnExistsModal');
                  if (el) { new bootstrap.Modal(el).show(); }
                }
              } else {
                alert('No existe un libro con ese ISBN.');
              }
            })
            .catch(function(){ alert('Error al verificar ISBN.'); });
        });
      }
    });
  </script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
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
  <div class="modal fade" id="isbnExistsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">ISBN duplicado</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="isbnModalText">Ese ISBN ya existe. ¿Quieres modificar el libro?</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <a id="isbnModalEditLink" class="btn btn-primary" href="#">Ir a editar</a>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <form method="post">
        <?= Session::csrfField() ?>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>ISBN (opcional)</label>
            <div class="input-group">
              <input id="isbnInput" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" />
              <div class="input-group-append">
                <button type="button" id="verifyIsbnBtn" class="btn btn-outline-secondary" title="Verificar ISBN">🔍</button>
              </div>
            </div>
          </div>
          <div class="form-group col-md-4">
            <label>Código de clasificación (opcional)</label>
            <input name="classification_code" class="form-control" value="<?= htmlspecialchars($_POST['classification_code'] ?? '') ?>" />
          </div>
          <div class="form-group col-md-4">
            <label>Etiqueta</label>
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
            <label>Título</label>
            <input required name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" />
          </div>
          <div class="form-group col-md-6">
            <label>Autor</label>
            <input required name="author" class="form-control" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Clasificación</label>
            <select required name="classification_id" class="form-control">
              <?php foreach ($classifications as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Origen</label>
            <select required name="origin_id" class="form-control">
              <?php foreach ($origins as $o): ?>
                <option value="<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Sala</label>
            <select required name="room_id" class="form-control">
              <?php foreach ($rooms as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['body']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Ejemplares</label>
            <input required type="number" min="1" name="copies_total" class="form-control" value="<?= htmlspecialchars($_POST['copies_total'] ?? '1') ?>" />
          </div>
          <div class="form-group col-md-9">
            <label>Observación</label>
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
