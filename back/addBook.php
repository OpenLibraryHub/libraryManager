<?php
include("conexion.php");
session_start();
if(!isset($_SESSION['correo'])) {
    header('Location: ../index.html');

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $con=conectar();
    $id= $_POST['LibrosID'];
    $addLibro = "UPDATE libros SET N_Disponible = N_Disponible +1, N_Ejemplares = N_Ejemplares + 1 WHERE id = ?";
    $stmt = $con->prepare($addLibro);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if ($stmt->affected_rows === 1) {

        echo "<script> alert('Libro agregado satisfactoriamente ')</script>";
        echo "<script> window.location = '../registrarLibro.php'; </script>";
    } else {
        echo "<script> alert('No se pudo agregar el libro intentelo nuevamente, y si el problema persiste contactese con el ingeniero ')</script>";
        echo "<script> window.location = '../registrarLibro.php'; </script>";
    }

}

?>