<?php

session_start();
if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
}elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener el ID enviado en la solicitud POST
    $id = $_POST["id"];
    $id2 = $_POST["id2"];
    // Conexión sda la base de datos
    include("back/conexion.php");
    $con=conectar();

    // Verificar la conexión
    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el ingeniero" . mysqli_connect_error();
        exit();
    }
    $addLibro = "UPDATE libros SET N_Disponible = N_Disponible +1 WHERE id = ?";
    $stmt = $con->prepare($addLibro);
    $stmt->bind_param("s", $id2);
    $stmt->execute();
    $sql = $con->prepare("UPDATE prestamos SET devuelto = TRUE , fecha_entregado = NOW() WHERE PrestamosID = ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    $con->close();

    if ($sql->affected_rows === 1) {

        header("location: prestamos.php");
    } else {
        echo "Error al eliminar el registro: " . $conn->error;
        header("location: prestamos.php");
    }
}
?>