<?php
include("conexion.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if(!isset($_SESSION['correo'])){
    header('Location: ../index.html');
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $con=conectar();

    if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero" . mysqli_connect_error();
        exit();
    }

    $LibrosID = $_POST["LibrosID"];
    $Titulo = $_POST["Titulo"];
    $Autor = $_POST["Autor"];
    $ClasificacionID = $_POST["ClasificacionID"];
    $CodigoClasificacion = $_POST["CodigoClasificacion"];
    $N_Ejemplares = $_POST["N_Ejemplares"];
    $OrigenID = $_POST["OrigenID"];
    $N_Disponible = $_POST["N_Disponible"];
    $EtiquetaID = $_POST["EtiquetaID"];
    $SalaID = $_POST["SalaID"];
    $Observacion = $_POST["Observacion"];

    $LibrosID = $LibrosID === "" ? NULL : $LibrosID;
    $CodigoClasificacion = trim($CodigoClasificacion) === "" ? NULL : $CodigoClasificacion;
    $Observacion = $Observacion === "" ? NULL : $Observacion;

      
    $createBook = "INSERT INTO libros(LibrosID, Titulo, Autor, ClasificacionID, CodigoClasificacion, N_Ejemplares, OrigenID, N_Disponible, EtiquetaID, BibliotecaID, SalaID, Observacion) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, 683070001001, ?, ?)";
    $stmt = $con->prepare($createBook);
    $stmt->bind_param("issssiiiiis", $LibrosID, $Titulo, $Autor, $ClasificacionID, $CodigoClasificacion, $N_Ejemplares, $OrigenID, $N_Disponible, $EtiquetaID, $SalaID, $Observacion );
    if ($stmt->execute()) {
        echo "<script> alert('Libro creado satisfactoriamente ')</script>";
        echo "<script> window.location = '../registrarLibro.php'; </script>";

    } else {

        echo "<script> alert('No se pudo crear el Libro intentelo nuevamente, y si el error persiste contactese con el Ingeniero')</script>";
        echo "<script> window.location = '../registrarLibro.php'; </script>";
    }
    
    $stmt->close();
    $con->close();
}
?>