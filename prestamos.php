<?php
session_start();
if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" defer=""></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js" defer=""></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" defer=""></script>
  <link rel="shortcut icon" href="https://cdn.pixabay.com/photo/2017/01/31/15/33/linux-2025130_1280.png" type="image/x-icon">
  
  <link rel="stylesheet" href="style/todos.css">
</head>
<body>
  
<?php
  include("nav.php");
?>





<!-- seccion buscador -->
<div class="container mt-4">
    <div class="row d-flex justify-content-center">
        <div class="col-md-6">
        <form class="input-group mb-3" method="get">
          <button class="btn btn-dark" id="buscar" type="button">Buscar por: </button>
          <select class="form-select" aria-label="Opcion" name="opcion" id="opcion">
            <option selected>Opcion</option>
            <option value="opcion1">Libro</option>
            <option value="opcion2">Nombre usuario</option>
            <option value="opcion3">Llave del saber</option>
        </select>
            <input style="width: 200px;" id="buscarImagen" type="search" class="form-control" placeholder="Buscar..." name="busqueda">
            <button class="btn btn-dark" id="buscar" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            </button>
</form>
        </div>
    </div>
</div>

<!-- muestra prestamos -->
<div id="api" class="contenedor row d-flex justify-content-center align-items-center">
  <div class="card card-body">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">Libro</th>
          <th scope="col">Nombre</th>
          <th scope="col">Apellido</th>
          <th scope="col">Correo</th>
          <th scope="col">Fecha prestamo</th>
          <th scope="col">Fecha limite</th>
          <th scope="col">Entregar</th>
          <th scope="col">Renovar</th>
        </tr>
      </thead>
      <tbody>
      <?php
  include("back/conexion.php");
  $con=conectar();
  if (mysqli_connect_errno()) {
    echo "Error al conectar a la base de datos: " . mysqli_connect_error();
    exit();
  }
  $a = $GET["busqueda"];

  if(isset($_GET['busqueda'])){

    $busqueda= $_GET['busqueda'];
    if($_GET['opcion']=='opcion1'){
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, us.Correo FROM prestamos AS pres 
      JOIN libros AS li ON li.id = pres.LibrosID
      JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE li.Titulo LIKE CONCAT('%', '$busqueda', '%') AND pres.devuelto = FALSE ORDER BY fecha_prestamo ASC
    ";
    }elseif($_GET['opcion']=='opcion2'){
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, us.Correo FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula  WHERE us.Nombre LIKE CONCAT('%' '$busqueda' '%') AND pres.devuelto = FALSE ORDER BY fecha_prestamo ASC ";
    }elseif($_GET['opcion']=='opcion3'){
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, us.Correo FROM prestamos AS pres 
      JOIN libros AS li ON pres.LibrosID = li.id
      JOIN usuarios AS us ON pres.UsuariosID = us.Cedula  WHERE us.Cedula LIKE CONCAT('%' '$busqueda' '%') AND pres.devuelto = FALSE ORDER BY fecha_prestamo ASC ";
    }else{
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, us.Correo FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE pres.devuelto = FALSE ORDER BY fecha_prestamo ASC
    ";
    }
    $resultBuscador = mysqli_query($con, $consultaBuscador);
    $usuarios = $resultBuscador->fetch_all(MYSQLI_ASSOC);
  }else{
    $consulta = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, pres.PrestamosID, li.Titulo, us.Nombre, us.Apellido, us.Correo FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE pres.devuelto = FALSE ORDER BY fecha_prestamo ASC
    ";
    $resultado = mysqli_query($con, $consulta);
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);  
  }
  
  foreach ($usuarios as $usuario) {  ?>
    <tr>
    <td> <?=$usuario['Titulo']?> </td>
    <td> <?=$usuario['Nombre']?> </td>
    <td> <?=$usuario['Apellido']?> </td>
    <td> <?=$usuario['Correo']?> </td>
    <td> <?=$usuario['fecha_prestamo']?> </td>
    <td> <?=$usuario['fecha_limite']?> </td>
    <td >
      
      <button id='<?=$usuario['PrestamosID']?>' libro='<?=$usuario['LibrosID']?>' class='prestamoEliminar btn btn-dark'>
        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-x-square' viewBox='0 0 16 16'>
            <path d='M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z'/>
            <path d='M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z'/>
        </svg>
      </button>
      <td>
        <form method="POST" action="./back/addTimePrestamos.php">
          <input name="prestamoID" type="hidden" value="<?= $usuario['PrestamosID'] ?>">
        <button  type="submit" class="btn btn-primary">
          <img src="./img/plus.svg" alt="agregar mas tiempo al prestamo">
        </button>
        </form>

    </td>
    </td>
  </tr>
  <?php }?>
      </tbody>
    </table>
  </div>
</div>

<form id="hiddenForm" action="eliminar_prestamo.php" method="post" style="display:none;">
  <input type="hidden" name="id" id="hiddenDato">
  <input type="hidden" name="id2" id="hiddenDato2">
</form>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.min.js"></script>
<script>
 
  let b1 = document.querySelectorAll('.prestamoEliminar');
  b1.forEach((boton1) => {
      boton1.addEventListener('click', (event) => {
        const areYouSure = confirm("El usuario entrego el libro ?");
        if(areYouSure) {
          document.getElementById("hiddenDato").value = boton1.id;
          document.getElementById("hiddenDato2").value = boton1.getAttribute('libro');
          document.getElementById("hiddenForm").submit();
        } else {
          alert("Libro no entregado");
        }

      });
  }); 
</script>
<div id="ultimo"></div>
</body>
</html>