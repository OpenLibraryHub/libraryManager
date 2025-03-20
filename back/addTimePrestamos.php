<?php
include("conexion.php");
session_start();
if(!isset($_SESSION['correo'])) {
    header('Location: ../index.html');

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prestamoID = $_POST["prestamoID"];
    $con=conectar();

    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero" . mysqli_connect_error();
        exit();
    }

    $consulta = $con->prepare("UPDATE prestamos SET fecha_limite = DATE_ADD(fecha_limite, INTERVAL 5 DAY) WHERE prestamosID = ? AND NOW() < fecha_limite");
    $consulta->bind_param("i", $prestamoID);
    $consulta->execute();

    if ($consulta->affected_rows === 1) {

        header("Location: ../prestamos.php");
    } else {
        header("Location: ../prestamos.php");
    }

    $con->close();
} else {
    header("Location: ../prestamos.php");

}
?>