<?php
include("conexion.php");
session_start();

class Persona{
  public $nombres; 
  public $apellidos;
  public $correo;
  public $cedula;
  public $llave;
  public $telefono;
  public $direccion;

  public function __construct($nombres, $apellidos, $correo, $cedula, $llave, $telefono, $direccion){
      $this->nombres=$nombres;
      $this->apellidos=$apellidos;
      $this->correo=$correo;
      $this->cedula=$cedula;
      $this->llave=$llave;
      $this->telefono=$telefono;
      $this->direccion=$direccion;
  }
}

if(!isset($_SESSION['correo'])){
  header('Location: ../index.html');
  exit();
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
$con=conectar();

if (mysqli_connect_errno()) {
  echo "Error al conectar a la base de datos: " . mysqli_connect_error();
  exit();
}


$persona=new Persona($_POST['nombres'], $_POST['apellidos'], $_POST['email'], $_POST['cedula'], $_POST['llave'], $_POST['telefono'], $_POST['direccion']);


$existQuery = $con->prepare("SELECT Correo FROM usuarios WHERE Correo = ? OR Cedula = ? OR UsuariosID = ? OR numero = ?");
$existQuery->bind_param("siii", $persona->correo, $persona->cedula, $persona->llave, $persona->telefono);
$existQuery->execute();
$result = $existQuery->get_result();
if ($result->num_rows > 0) {
  $con->close();
  echo "<script>alert('El usuario ya existe')</script>";
  echo "<script> window.location = '../usuarios.php'; </script>";
  exit();
} else {
  /* $UsuariosID = hash('sha256', $persona->llave);
  $Cedula = hash('sha256', $persona->cedula); */
  $UsuariosID =$persona->llave;
  $Cedula = $persona->cedula;

  $query = $con->prepare("INSERT INTO usuarios (UsuariosID, Nombre, Apellido, Correo, Cedula, Fecha, numero, direccion) VALUES (?,?,?,?,?,NOW(),?,?)");
  $query->bind_param("isssiis",$UsuariosID, $persona->nombres, $persona->apellidos, $persona->correo, $Cedula, $persona->telefono, $persona->direccion);
  $query->execute();
  $con->close();

  if ($query->affected_rows === 1) {
     echo "<script> alert('Usuario Registrado Satisfactoriamente')</script>";
    echo "<script> window.location = '../usuarios.php'; </script>";
  } else {
    echo "<script> alert('Error al registrar el usuario, porfavor revisar los datos')</script>";
    echo "<script> window.location = '../usuarios.php'; </script>";
    
  }
}

} else {
  header("Location: ../index.html");
}




?>
