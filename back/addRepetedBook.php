<?php
include("conexion.php");
header("Content-Type: application/json");
session_start();
if(!isset($_SESSION['correo'])) {
    header('Location: ../index.html');

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $con=conectar();
    $data = file_get_contents('php://input');
    $query = $con->prepare("SELECT l.id AS id, l.LibrosID, l.Titulo, l.Autor, c.Descripcion AS ClasificacionID, l.CodigoClasificacion, e.Descripcion AS EtiquetaID, s.Descripcion AS sala FROM libros l LEFT JOIN clasificacion c ON l.ClasificacionID = c.ClasificacionID LEFT JOIN origen o ON l.OrigenID = o.OrigenID LEFT JOIN etiqueta e ON l.EtiquetaID = e.EtiquetaID LEFT JOIN sala s ON l.salaID = s.salaID  WHERE LibrosID = ? ");
    $query->bind_param("s", $data);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    $con->close();
    $rows = array();
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;

    }
    echo json_encode($rows);
}


?>