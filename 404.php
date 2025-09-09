<?php
http_response_code(404);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath = ($dir === '/' ? '' : $dir);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Página no encontrada</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .vh-80 { min-height: 80vh; }
  </style>
  <meta http-equiv="Cache-Control" content="no-store" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <link rel="icon" href="data:,">
  </head>
<body>
  <div class="container d-flex align-items-center justify-content-center vh-80">
    <div class="text-center">
      <h1 class="display-3 text-muted">404</h1>
      <h2 class="mb-3">Ups, creo que no encontramos lo que estás buscando.</h2>
      <p class="text-secondary mb-4">La página solicitada no existe o fue movida.</p>
      <div class="d-flex justify-content-center">
        <a href="<?= htmlspecialchars($basePath) ?>/login.php" class="btn btn-primary mr-2">Ir al inicio de sesión</a>
        <a href="<?= htmlspecialchars($basePath) ?>/catalog.php" class="btn btn-outline-secondary">Ver catálogo</a>
      </div>
    </div>
  </div>
</body>
</html>


