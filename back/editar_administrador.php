<?php
include("conexion.php");
session_start();

if(!isset($_SESSION['correo'])){
    header('Location: ../index.html');
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $userId = $_SESSION['idUsuario'];

    $con=conectar();

    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero" . mysqli_connect_error();
        exit();
    }
    
    $consulta = $con->prepare("UPDATE librarians SET first_name =?, paternal_last_name=?, email=?, password=? WHERE id =?");

    $consulta->bind_param("ssssi" , $nombre, $apellido, $email, $password, $userId);

    $consulta->execute();
    $con->close();

    if ($consulta->affected_rows === 1) {
        $_SESSION['nombre']=$nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['correo'] = $email;
        $_SESSION['password'] = $password;
        header("Location: ../usuarios.php");
    } else {
        header("Location: ../usuarios.php");
    }

    $con->close();
} else {
    header('Location: ../index.html');
}
?>
