<?php
require_once __DIR__ . '/config/autoload.php';

use App\Helpers\Session;
use App\Models\Book;

Session::start();

$q = trim($_GET['q'] ?? '');
$field = $_GET['field'] ?? 'all';
$allowedFields = ['all','title','author','isbn','code'];
if (!in_array($field, $allowedFields, true)) { $field = 'all'; }
$onlyAvailable = isset($_GET['available']) ? (int)$_GET['available'] === 1 : 1;

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
$limit = 20;
$offset = ($page - 1) * $limit;

$bookModel = new Book();
$total = 0;
$books = [];

if ($q !== '') {
  $matches = $bookModel->searchBooks($q, $field, $onlyAvailable);
  $total = count($matches);
  $books = array_slice($matches, $offset, $limit);
} else {
  $total = $bookModel->countAll($onlyAvailable);
  $books = $bookModel->getPage($limit, $offset, $onlyAvailable);
}

$totalPages = max(1, (int)ceil($total / $limit));

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .archived-badge { font-size: 12px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Catálogo</h3>
    <div>
      <?php if (Session::isLoggedIn()): ?>
        <a class="btn btn-outline-secondary" href="account.php">Mi cuenta</a>
        <a class="btn btn-outline-danger" href="logout.php">Salir</a>
      <?php else: ?>
        <a class="btn btn-primary" href="login.php">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>

  <form method="get" class="card mb-3">
    <div class="card-body">
      <div class="form-row align-items-end">
        <div class="col-md-5">
          <label for="q">Buscar</label>
          <input id="q" name="q" type="text" class="form-control" value="<?= h($q) ?>" placeholder="Título, autor, ISBN o código">
        </div>
        <div class="col-md-3">
          <label for="field">Campo</label>
          <select id="field" name="field" class="form-control">
            <option value="all" <?= $field==='all'?'selected':'' ?>>Todos</option>
            <option value="title" <?= $field==='title'?'selected':'' ?>>Título</option>
            <option value="author" <?= $field==='author'?'selected':'' ?>>Autor</option>
            <option value="isbn" <?= $field==='isbn'?'selected':'' ?>>ISBN</option>
            <option value="code" <?= $field==='code'?'selected':'' ?>>Código</option>
          </select>
        </div>
        <div class="col-md-2">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" value="1" id="available" name="available" <?= $onlyAvailable? 'checked':'' ?>>
            <label class="form-check-label" for="available">Solo disponibles</label>
          </div>
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary btn-block" type="submit">Buscar</button>
        </div>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th>Título</th>
              <th>Autor</th>
              <th>ISBN</th>
              <th>Código</th>
              <th>Ubicación</th>
              <th>Disponibilidad</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($books as $b): ?>
              <?php 
                $archived = App\Models\Book::isArchivedRow($b);
                $availableCount = (int)($b['copies_available'] ?? 0);
              ?>
              <tr>
                <td>
                  <a href="book.php?id=<?= (int)$b['id'] ?>"><?= h($b['title'] ?? '') ?></a>
                  <?php if ($archived): ?>
                    <span class="badge badge-secondary archived-badge">Archivado</span>
                  <?php endif; ?>
                </td>
                <td><?= h($b['author'] ?? '') ?></td>
                <td><?= h($b['isbn'] ?? '') ?></td>
                <td><?= h($b['classification_code'] ?? '') ?></td>
                <td><?= h($b['room'] ?? '') ?></td>
                <td>
                  <?php if ($availableCount > 0): ?>
                    <span class="text-success">Disponible (<?= (int)$availableCount ?>)</span>
                  <?php else: ?>
                    <span class="text-danger">No disponible</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <a class="btn btn-sm btn-outline-primary" href="book.php?id=<?= (int)$b['id'] ?>">Ver</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($books)): ?>
              <tr><td colspan="7" class="text-center text-muted">Sin resultados</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <nav>
        <ul class="pagination mb-0">
          <?php 
            $base = $_GET; 
            $base['page'] = 1; 
            $firstUrl = '?' . http_build_query($base);
            $base['page'] = max(1, $page-1);
            $prevUrl = '?' . http_build_query($base);
            $base['page'] = min($totalPages, $page+1);
            $nextUrl = '?' . http_build_query($base);
            $base['page'] = $totalPages;
            $lastUrl = '?' . http_build_query($base);
          ?>
          <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h($firstUrl) ?>">Primera</a></li>
          <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h($prevUrl) ?>">Anterior</a></li>
          <li class="page-item disabled"><span class="page-link">Página <?= (int)$page ?> de <?= (int)$totalPages ?></span></li>
          <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= h($nextUrl) ?>">Siguiente</a></li>
          <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= h($lastUrl) ?>">Última</a></li>
        </ul>
      </nav>
    </div>
  </div>

</div>
</body>
</html>


