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
          <th scope="col">Fecha de prestamo</th>
          <th scope="col">Fecha de vencimiento</th>
          <th scope="col">Fecha de Entrega</th>
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
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, pres.fecha_entregado FROM prestamos AS pres 
      JOIN libros AS li ON li.id = pres.LibrosID
      JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE li.Titulo LIKE CONCAT('%', '$busqueda', '%') AND pres.devuelto = TRUE  ORDER BY fecha_prestamo ASC
    ";
    }elseif($_GET['opcion']=='opcion2'){
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, pres.fecha_entregado FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula  WHERE us.Nombre LIKE CONCAT('%' '$busqueda' '%') AND pres.devuelto = TRUE ORDER BY fecha_prestamo ASC ";
    }elseif($_GET['opcion']=='opcion3'){
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, pres.fecha_entregado FROM prestamos AS pres 
      JOIN libros AS li ON pres.LibrosID = li.id
      JOIN usuarios AS us ON pres.UsuariosID = us.Cedula  WHERE us.Cedula LIKE CONCAT('%' '$busqueda' '%') AND pres.devuelto = TRUE ORDER BY fecha_prestamo ASC ";
    }else{
      $consultaBuscador = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, li.Titulo, us.Nombre, us.Apellido, pres.fecha_entregado FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE pres.devuelto = TRUE ORDER BY fecha_prestamo ASC
    ";
    }
    $resultBuscador = mysqli_query($con, $consultaBuscador);
    $usuarios = $resultBuscador->fetch_all(MYSQLI_ASSOC);
  }else{
    $consulta = "SELECT pres.fecha_prestamo, pres.LibrosID, pres.fecha_limite, pres.PrestamosID, li.Titulo, us.Nombre, us.Apellido, pres.fecha_entregado FROM prestamos AS pres 
    JOIN libros AS li ON pres.LibrosID = li.id
    JOIN usuarios AS us ON pres.UsuariosID = us.Cedula WHERE pres.devuelto = TRUE ORDER BY fecha_prestamo ASC
    ";
    $resultado = mysqli_query($con, $consulta);
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);  

  }
  
  foreach ($usuarios as $usuario) {  ?>
    <tr>
    <td> <?=$usuario['Titulo']?> </td>
    <td> <?=$usuario['Nombre']?> </td>
    <td> <?=$usuario['Apellido']?> </td>
    <td> <?=$usuario['fecha_prestamo']?> </td>
    <td> <?=$usuario['fecha_limite']?> </td>
    <td> <?=$usuario['fecha_entregado']?> </td>

  </tr>
  <?php }?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.min.js"></script>
<script>
  let b = document.querySelectorAll('.prestamoEditar');
    b.forEach((boton) => {
        boton.addEventListener('click', (event) => {
          console.log(boton.id)
          let libro = document.querySelector('#libro');
          let prestador = document.querySelector('#prestador');
          let fecha_prestamo = document.querySelector('#fecha_prestamo');
          let fecha_vencimiento = document.querySelector('#fecha_vencimiento');
          // Obtén el modal por su ID
          var modal = document.getElementById("exampleModal7");
          // Crea una instancia del modal usando el constructor Modal de Bootstrap
          var modalInstance = new bootstrap.Modal(modal);
          // Activa el modal
          modalInstance.show();
          libro.value = document.getElementById('a' + boton.id).textContent;
          prestador.value= document.getElementById('b' + boton.id).textContent;
          fecha_prestamo.value= document.getElementById('c' + boton.id).textContent;
          fecha_vencimiento.value= document.getElementById('d' + boton.id).textContent;
          idd.value= boton.id;
        });
    });  

  //Eliminar
    let b1 = document.querySelectorAll('.prestamoEliminar');
  b1.forEach((boton1) => {
      boton1.addEventListener('click', (event) => {
      console.log(boton1.id)
      /* const botonClicado1 = event.target;
      const posicion1 = Array.from(b1).indexOf(botonClicado1);
      console.log(botonClicado1)  */
      document.getElementById("hiddenDato").value = boton1.id;
      document.getElementById("hiddenDato2").value = boton1.getAttribute('libro');
      document.getElementById("hiddenForm").submit();
      });
  }); 
</script>
<div id="ultimo"></div>
</body>
</html>