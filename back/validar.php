<?php
include("conexion.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
$con=conectar();
if (mysqli_connect_errno()) {
    echo "Error al conectar a la base de datos comuniquese con el Ingeniero" . mysqli_connect_error();
  exit();
}


$correo = mysqli_real_escape_string($con, $_POST['email']); 
$pasw = mysqli_real_escape_string($con, $_POST['password']); 

$consulta = $con->prepare("SELECT * FROM librarians WHERE email=? ");

$consulta->bind_param("s" , $correo);

$consulta->execute();

$result = $consulta->get_result();



$con->close();

if ($result->num_rows === 1) {

    $row = $result->fetch_assoc();
    if (password_verify($pasw, $row['password'])) {
    session_start();
    $_SESSION['idUsuario'] = $row['id'];
    $_SESSION['correo'] = $row['email'];
    $_SESSION['nombre'] = $row['first_name'];
    $_SESSION['apellido'] = $row['paternal_last_name'];
    $_SESSION['password'] = $row['password'];
    header("Location: ../usuarios.php"); 
    exit();

    } else {
        echo "Contraseña incorrecta";
    }

    
} else {
    header("Location: ../index.html");
}
} else {
    header("Location: ../index.html");
}   

?>