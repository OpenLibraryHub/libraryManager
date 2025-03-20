<!-- barra de navegacion -->
<nav class='navbar navbar-expand-lg navbar-dark bg-warning py-1'>
    <a class='navbar-brand' href='#'>
        <img class='imgLogo' src='./img/book.svg' alt=''>
        Administrador
    </a>
    <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
        <span class='navbar-toggler-icon'></span>
    </button>
    <div class='collapse navbar-collapse' id='navbarNav'>
        <ul class='navbar-nav'>            
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link' href='#' data-toggle='modal' data-target='#exampleModal6'><button type='button' class='btn btn-success'>Registrar usuario</button></a>
        </li>
        <li class='nav-item d-flex align-items-center'>
            <a class='nav-link' href='prestar.php'><button type='button' class='btn btn-success'>Prestar</button></a>
        </li>
        <li class='nav-item'>
            <a class='nav-link' href='prestamos.php'><button type='button' class='btn btn-success'>Prestamos</button></a>
        </li>
        <li class='nav-item'>
            <a class='nav-link' href='devoluciones.php'><button type='button' class='btn btn-success'>Devoluciones</button></a>
        </li>
        <li class='nav-item'>
            <a class='nav-link' href='usuarios.php'><button type='button' class='btn btn-success'>Usuarios</button></a>
        </li>
        <li class='nav-item'>
            <a class='nav-link' href='registrarLibro.php'><button type='button' class='btn btn-success'>Registrar Libro</button></a>
        </li>
        </ul>
        <!-- cambiar esta parte para que se inice sesion y en caso de haber iniciado sesion lo dirija a las instrucciones para hacer fecth API -->
        <button type='button' class='nav-link  ml-auto btn btn-success'  data-toggle='modal' data-target='#exampleModal10'><?=  $_SESSION['nombre']; ?></button>
        <img src='imagenperfil.png' class='ml-2 imgLogo' alt=''>
    </div>
</nav>

<!-- modal para de opciones para el administrador #10-->
<div class="modal fade" id="exampleModal10" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog mt-0 mb-2 h-100 d-flex justify-content-center align-items-center" role="document">
    <div class="modal-content contenidoModal">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Opciones administrador</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body d-flex justify-content-center flex-column">
        <a class="w-100 d-flex justify-content-center" data-toggle='modal' data-target='#exampleModal11'><button type="submit" class="btn btn-success m-2">Editar Perfil</button></a>

        <a href="back/downloadExcel.php" class="w-100 d-flex justify-content-center" > <button class="btn btn-success m-2"> Descargar Prestamos </button> </a>

         <button class="btn btn-success m-2" id="showVolunteers" data-dismiss="modal" aria-label="Close" type="button"> Voluntarios creadores del Proyecto Biblioteca 😀 </button>

        <a href="back/salir.php" class="w-100 d-flex justify-content-center"><button type="submit" class="btn btn-success m-2">Cerrar sesion</button></a>
      </div>
      <div class="modal-footer">
      <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="btn btn-dark">Cerrar</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- modal para editar administrador #11-->
<div class="modal fade" id="exampleModal11" tabindex="2" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog mt-0 mb-2 h-100 d-flex justify-content-center align-items-center" role="document">
    <div class="modal-content contenidoModal">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Editar administrador</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
          <form method="POST" action="back/editar_administrador.php">
              <div class="form-group  text-white">
                <label for="nombre">Nombres</label>
                <input required type="text" class="form-control" name="nombre" id="nombre" value="<?= $_SESSION['nombre'] ?>" required>
              </div>
              <div class="form-group  text-white">
                <label for="apellido">Apellidos</label>
                <input required type="text" class="form-control" name="apellido" id="apellido" value="<?php echo $_SESSION['apellido']; ?>" required>
              </div>
              <div class="form-group  text-white">
                <label for="email">Correo electrónico</label>
                <input required type="email" class="form-control" name="email" id="email" value="<?= $_SESSION['correo'] ?>" required>
              </div>
              <div class="form-group  text-white">
                <label for="cedula">Contraseña</label>
                <input required type="text" class="form-control" name="password" id="password" value="<?= $_SESSION['password'] ?>" required>
              </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="submit" class="btn btn-success">Editar</button>
        </form>
            <button type="button" class="btn btn-dark" data-dismiss="modal" aria-label="Close">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- modal para registrar usuario #6-->
<div class="modal fade" id="exampleModal6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog mt-0 mb-2 h-100 d-flex justify-content-center align-items-center" role="document">
    <div class="modal-content contenidoModal">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="exampleModalLabel">Registrar usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
          <form method="POST" action="back/registrar_usuario.php">
            
              <div class="form-group  text-white">
                <label for="nombre">Nombres</label>
                <input required type="text" class="form-control" name="nombres" id="nombre" placeholder="Ingresa nombres">
              </div>

              <div class="form-group  text-white">
                <label for="apellido">Apellidos</label>
                <input required type="text" class="form-control" name="apellidos" id="apellido" placeholder="Ingresa apellidos">
              </div>

              <div class="form-group  text-white">
                <label for="email">Correo electrónico</label>
                <input required type="email" class="form-control" name="email" id="email" placeholder="Ingresa correo electrónico">
              </div>

              <div class="form-group  text-white">
                <label for="cedula">Cedula</label>
                <input required type="number" class="form-control" name="cedula" id="cedula" placeholder="Ingresa cedula">
              </div>

              <div class="form-group  text-white">
                <label for="llave">Llave del saber</label>
                <input required type="number" class="form-control" name="llave" id="llave" placeholder="Ingresa llave del saber">
              </div>

                <div class="form-group  text-white">
                <label for="llave">Telefono</label>
                <input required type="number" class="form-control" name="telefono" id="telefono" placeholder="Ingresa el numero del telefono">
                </div>

                <div class="form-group  text-white">
                <label for="llave">Direccion</label>
                <input required type="text" class="form-control" name="direccion" id="direccion" placeholder="Ingresa su direccion">
                </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="submit" class="btn btn-success">Registrar</button>
        </form>
        <button type="button" class="close text-white " data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="btn btn-dark">Cerrar</span>
        </button>
      </div>
    </div>
  </div>
</div>


<dialog id="volunteers-modal" class="hide-modal">
        
          <article class="aritcle-thanks">
            <h3> <strong>PROYECTO BIBLIOTECA</strong> desarrollado por: </h3>
            <p> <strong> Luis Mantilla (Matematico): </strong> creador de la Base de Datos. Perfil: <a title="Perfil de Github del desarrollador Luis Mantilla" target="_blank" href="https://github.com/LuisMantilla28"> Github </a></p>
            <p> <strong> Jhon Pabon (Desarrollador): </strong> Desarrollador PHP. Perfil: <a title="Perfil de Github del desarrollador Jhon Pabon" target="_blank" href="https://github.com/JohnPabon"> Github </a> </p>
            <p> <strong> Cesar Andres  (Ingeniero Mecanico): </strong> Desarrollador PHP. Perfil: <a title="Perfil de Github del desarrollador Cesar Andres" target="_blank" href="https://github.com/andressantage"> Github </a> </p>
            <p> <strong> Maicol Estrada (Diseñador): </strong> Diseñador de la interfaz del Proyecto. Perfil: <a title="Perfil de Behance del  diseñador Maicol Estrada" target="_blank" href="https://www.behance.net/Estrada314"> Behance </a> </p>
            <p> <strong> Ricardo Franco (Desarrollador): </strong> Desarrollador PHP. Perfil: <a title="Perfil de Github del desarrollador Ricardo Franco" target="_blank" href="https://github.com/riadfrancoq"> Github </a> </p>
          </article>
          <button type="button" id="close-volunteers-modal"> Cerrar </button>

</dialog>

<script>
  const showVolunteers = document.querySelector("#showVolunteers");
  const modal = document.getElementById('volunteers-modal');
  const closeVolunteersModal = document.getElementById('close-volunteers-modal');
  closeVolunteersModal.addEventListener('click', (e) => {
    e.preventDefault();
    modal.close();
  });
  showVolunteers.addEventListener('click',(e)=> { 
    e.preventDefault();
    modal.showModal();

  });
</script>

<style>
  #openModal {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
}



#volunteers-modal {
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 20px;
    max-width: 700px;
    width: 100%;
    text-align: center;
    display: none; 

}

#volunteers-modal[open] {
    display: grid;
    place-items: center;
    position: fixed;
    align-items: start;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

#volunteers-modal::backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

#close-volunteers-modal { 
    border: none;
    padding: 0.4rem;
    margin-top: 0.3rem;
    border-radius: 10%;
}

.aritcle-thanks {
  display: flex;
  flex-direction: column;
  align-items: start;
  justify-content: start;
}

</style>