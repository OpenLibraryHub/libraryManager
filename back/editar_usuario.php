<?php

include("conexion.php");
session_start();

if(!isset($_SESSION['correo'])) {
    header('Location: ../index.html');

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $email = $_POST["email"];
    $cedula = $_POST["cedula"];
    $llave = $_POST["llave"];
    $telefono = $_POST["telefono"];
    $direccion = $_POST["direccion"];
    $hiddeninfo = $_POST["hiddenId"];

    $con=conectar();

    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero" . mysqli_connect_error();
        exit();
    }

    $consulta = $con->prepare("UPDATE usuarios SET UsuariosID = ?, Nombre = ?, Apellido= ? , Correo= ? , Cedula= ? , numero = ? , direccion = ? WHERE Cedula = ?");
    $consulta->bind_param("isssiisi", $llave, $nombres, $apellidos, $email, $cedula, $telefono, $direccion, $hiddeninfo);
    $consulta->execute();
    $con->close();


    if ($consulta->affected_rows === 1) {
        header("Location: ../usuarios.php");

    } else {
        header("Location: ../usuarios.php");
    }

}else { 
    header('Location: ../index.html');

}
?>

