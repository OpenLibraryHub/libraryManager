<?php
require_once __DIR__ . '/config/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Helpers\Session;
use App\Models\Book;

AuthMiddleware::require();
Session::start();

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$onlyAvailable = isset($_GET['available']) ? ($_GET['available'] == '1') : false;
$q = trim((string)($_GET['q'] ?? ''));
$field = $_GET['field'] ?? 'all';

$bookModel = new Book();
if ($q !== '') {
  $books = $bookModel->searchBooks($q, $field, $onlyAvailable);
  $total = count($books);
  $pages = 1;
  $page = 1;
} else {
  $total = $bookModel->countAll($onlyAvailable);
  $pages = max(1, (int)ceil($total / $perPage));
  $page = min($page, $pages);
  $offset = ($page - 1) * $perPage;
  $books = $bookModel->getPage($perPage, $offset, $onlyAvailable);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Libros</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <?= Session::csrfMeta() ?>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Libros (<?= htmlspecialchars((string)$total) ?>)</h3>
    <div>
      <a href="dashboard.php" class="btn btn-outline-dark mr-2">Dashboard</a>
      <a href="books_create.php" class="btn btn-success mr-1">Nuevo libro</a>
      <a href="loans.php" class="btn btn-primary mr-1">Préstamos</a>
      <a href="returns.php" class="btn btn-secondary">Devoluciones</a>
    </div>
  </div>

  <form class="form-inline mb-3" method="get">
    <div class="form-row align-items-end w-100">
      <div class="col-md-3 mb-2">
        <label class="small text-muted d-block">Búsqueda</label>
        <input class="form-control" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Título, autor, ISBN...">
      </div>
      <div class="col-md-3 mb-2">
        <label class="small text-muted d-block">Campo</label>
        <select class="form-control" name="field">
          <option value="all" <?= $field==='all'?'selected':'' ?>>Todos</option>
          <option value="title" <?= $field==='title'?'selected':'' ?>>Título</option>
          <option value="author" <?= $field==='author'?'selected':'' ?>>Autor</option>
          <option value="isbn" <?= $field==='isbn'?'selected':'' ?>>ISBN</option>
          <option value="code" <?= $field==='code'?'selected':'' ?>>Código</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label class="small text-muted d-block">Disponibilidad</label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="available" value="1" id="availableCheck" <?= $onlyAvailable ? 'checked' : '' ?>>
          <label class="form-check-label" for="availableCheck">Solo disponibles</label>
        </div>
      </div>
      <div class="col-md-3 mb-2">
        <button class="btn btn-outline-primary mr-2" type="submit">Aplicar</button>
        <a class="btn btn-outline-secondary" href="books.php">Limpiar</a>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>ID</th>
            <th>ISBN</th>
            <th>Título</th>
            <th>Autor</th>
            <th>Clasificación</th>
            <th>Disponibles</th>
            <th>Sala</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $b): ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= htmlspecialchars((string)($b['isbn'] ?? '')) ?></td>
            <td><a href="books_detail.php?id=<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title'] ?? '') ?></a></td>
            <td><?= htmlspecialchars($b['author'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['classification'] ?? '') ?></td>
            <td>
              <?php if (\App\Models\Book::isArchivedRow($b)): ?>
                <span class="badge badge-secondary">Archivado</span>
              <?php else: ?>
                <?= (int)($b['copies_available'] ?? 0) ?> / <?= (int)($b['copies_total'] ?? 0) ?>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b['room'] ?? '') ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="books_edit.php?id=<?= (int)$b['id'] ?>">Editar</a>
              <a class="btn btn-sm btn-outline-danger" href="books_delete.php?id=<?= (int)$b['id'] ?>">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
          <tr><td colspan="7" class="text-center text-muted">No hay libros</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($pages > 1): ?>
    <nav aria-label="Paginación" class="mt-3">
      <ul class="pagination">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&available=<?= $onlyAvailable?1:0 ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>">Anterior</a>
        </li>
        <?php for ($p = max(1,$page-2); $p <= min($pages, $page+2); $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&available=<?= $onlyAvailable?1:0 ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?>&available=<?= $onlyAvailable?1:0 ?>&q=<?= urlencode($q) ?>&field=<?= htmlspecialchars($field) ?>">Siguiente</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>
</body>
</html>
