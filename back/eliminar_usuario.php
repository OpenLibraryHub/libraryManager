<?php
include("conexion.php");
session_start();

if(!isset($_SESSION['correo'])){
    header('Location: ../index.html');
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $con=conectar();

    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero" . mysqli_connect_error();
        exit();
    }

    $consulta = $con->prepare("DELETE FROM usuarios WHERE Cedula = ?");

    $consulta->bind_param("i", $id);
    $consulta->execute();
    $co->close();
    if ($consulta->affected_rows === 1) {
        header("Location: ../usuarios.php");
    } else {
        header("Location: ../usuarios.php");

    }
    $con->close();
}
?>
