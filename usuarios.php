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
include('nav.php');
?>




<!-- seccion buscador -->
<div class="container mt-4">
    <div class="row d-flex justify-content-center">
        <div class="col-md-6">
        <form class="input-group mb-3" method="get">
          <button class="btn btn-dark" id="buscar" type="button">Buscar por: </button>
          <select class="form-select" aria-label="Opcion" name="opcion" id="opcion">
            <option selected>Opcion</option>
            <option value="opcion1">Cedula</option>
            <option value="opcion2">Nombre</option>
            <option value="opcion3">Apellido</option>
            <option value="opcion4">Correo</option>
            <option value="opcion5">LLave saber</option>
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

<!-- muestra usuarios -->
<div id="api" class="contenedor row d-flex justify-content-center align-items-center">
  <div class="card card-body">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">Cedula </th>
          <th scope="col">Nombre </th>
          <th scope="col">Apellido </th>
          <th scope="col">Correo </th>
          <th scope="col">Llave saber </th>
          <th scope="col"> Telefono </th>
          <th scope="col"> Direccion </th>
          <th scope="col"> Sancionado </th>
          <th scope="col" >Accion</th>
        </tr>
      </thead>
      <tbody>
<?php
  include("back/conexion.php");
  $con=conectar();
  if (mysqli_connect_errno()) {
    echo "Error al conectar a la base de datos: comuniquese con el Ingeniero " . mysqli_connect_error();
    exit();
  }

  if(isset($_GET['busqueda'])){
    $busqueda= $_GET['busqueda'];
    if($_GET['opcion']=='opcion1'){
      $consultaBuscador = "SELECT * FROM usuarios WHERE Cedula LIKE '%$busqueda%' ";
    }elseif($_GET['opcion']=='opcion2'){
      $consultaBuscador = "SELECT * FROM usuarios WHERE Nombre LIKE '%$busqueda%' ";
    }elseif($_GET['opcion']=='opcion3'){
      $consultaBuscador = "SELECT * FROM usuarios WHERE Apellido LIKE '%$busqueda%' ";
    }elseif($_GET['opcion']=='opcion4'){
      $consultaBuscador = "SELECT * FROM usuarios WHERE Correo LIKE '%$busqueda%' ";
    }elseif($_GET['opcion']=='opcion5'){
      $consultaBuscador = "SELECT * FROM usuarios WHERE UsuariosID LIKE '%$busqueda%' ";
    }else{
      $consultaBuscador = "SELECT * FROM usuarios ORDER BY Fecha DESC";
    }
    $resultBuscador = mysqli_query($con, $consultaBuscador);
    $usuarios = $resultBuscador->fetch_all(MYSQLI_ASSOC);
  }else{
    $consulta = "SELECT * FROM usuarios ORDER BY Fecha DESC";
    $resultado = mysqli_query($con, $consulta);
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
  }
  
  foreach ($usuarios as $usuario) {  ?>
    <tr>
      <th id='a<?=$usuario['Cedula']?>' scope='row'><?=$usuario['Cedula']?></th>
      <td id='b<?=$usuario['Cedula']?>'><?=$usuario['Nombre']?></td>
      <td id='c<?=$usuario['Cedula']?>'><?=$usuario['Apellido']?></td>
      <td id='d<?=$usuario['Cedula']?>'><?=$usuario['Correo']?></td>
      <td id='e<?=$usuario['Cedula']?>'><?=$usuario['UsuariosID']?></td>
      <td id='f<?=$usuario['Cedula']?>'><?=$usuario['numero']?></td>
      <td id='g<?=$usuario['Cedula']?>'><?=$usuario['direccion']?></td>
      <td > <?=$usuario['sancionado'] ? "SI" : "NO" ?></td>
      <td class='text-center'>
      <button id='<?=$usuario['Cedula']?>' class='prestamoEditar btn btn-primary'>
          <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil' viewBox='0 0 16 16'>
          <path d='M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z'/>
          </svg>
      </button>
     
      </td>
    </tr>
  <?php }?>
      </tbody>
    </table>
  </div>
</div>

<!-- modal para editar usuario #7-->
<div class="modal fade" id="exampleModal7" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog mt-0 mb-2 h-100 d-flex justify-content-center align-items-center" role="document">
    <div class="modal-content contenidoModal">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Editar usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="back/editar_usuario.php">
          <div class="form-group  text-white">
            <label for="nombre">Nombres</label>
            <input required type="text" class="form-control" name="nombres" id="nombre7" placeholder="Ingresa nombres">
          </div>
          <div class="form-group  text-white">
            <label for="apellido">Apellidos</label>
            <input required type="text" class="form-control" name="apellidos" id="apellido7" placeholder="Ingresa apellidos">
          </div>
          <div class="form-group  text-white">
            <label for="email">Correo electrónico</label>
            <input required type="email" class="form-control" name="email" id="email7" placeholder="Ingresa correo electrónico">
          </div>
          <div class="form-group  text-white">
            <label for="cedula">Cedula</label>
            <input required type="number" class="form-control" name="cedula" id="cedula7" placeholder="Ingresa cedula">
          </div>
          <div class="form-group  text-white">
            <label for="llave">Llave del saber</label>
            <input required type="number" class="form-control" name="llave" id="llave7" placeholder="Ingresa llave del saber">
          </div>
          <div class="form-group  text-white">
            <label for="llave">Telefono</label>
            <input required type="number" class="form-control" name="telefono" id="telefono7" placeholder="Ingresa llave del saber">
          </div>
          <div class="form-group  text-white">
            <label for="llave">Direccion</label>
            <input required type="text" class="form-control" name="direccion" id="direccion7" placeholder="Ingresa llave del saber">
          </div>
          <input id="hiddeninfo" name="hiddenId" type="hidden" />
          <div class="modal-footer justify-content-between">
        <button type="submit" class="btn btn-success">Editar</button>
        <a href="usuarios.php"><button type="button" class="btn btn-dark">Cerrar</button></a>
      </div>
      </div>

        </form>

    </div>
  </div>
</div>


<form id="hiddenForm" action="back/eliminar_usuario.php" method="post" style="display:none;">
  <input type="hidden" name="id" id="hiddenDato">
</form>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.min.js"></script>
<script>
  let b = document.querySelectorAll('.prestamoEditar');
    b.forEach((boton) => {
      boton.addEventListener('click', (event) => {
        console.log(boton.id)
        let nombre = document.querySelector('#nombre7');
        let apellido = document.querySelector('#apellido7');
        let email = document.querySelector('#email7');
        let cedula = document.querySelector('#cedula7');
        let llave = document.querySelector('#llave7');
        let telefono = document.querySelector('#telefono7');
        let direccion = document.querySelector('#direccion7');
        let hiddeninfo = document.querySelector('#hiddeninfo');
        console.log(hiddeninfo);
        // Obtén el modal por su ID
        var modal = document.getElementById("exampleModal7");
        // Crea una instancia del modal usando el constructor Modal de Bootstrap
        var modalInstance = new bootstrap.Modal(modal);
        // Activa el modal
        modalInstance.show();

        nombre.value = document.getElementById('b' + boton.id).textContent;
        console.log('b' + boton.id)
        apellido.value= document.getElementById('c' + boton.id).textContent;
        email.value= document.getElementById('d' + boton.id).textContent;
        llave.value= document.getElementById('e' + boton.id).textContent;
        telefono.value = document.getElementById('f' + boton.id).textContent;
        direccion.value = document.getElementById('g' + boton.id).textContent;
        cedula.value= boton.id;
        hiddeninfo.value = boton.id;
      });
    });  

  //Eliminar
    let b1 = document.querySelectorAll('.prestamoEliminar');
  b1.forEach((boton1) => {
      boton1.addEventListener('click', (event) => {
      console.log(boton1.id)
      document.getElementById("hiddenDato").value = boton1.id;
      document.getElementById("hiddenForm").submit();
      });
  }); 
</script>
<div id="ultimo"></div>
</body>
</html>