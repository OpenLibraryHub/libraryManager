<?php
// Connection 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
session_start();
if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
} else {
    $filename = "ReporteLibrosPrestados.xls";
    header("Content-Disposition: attachment; filename=\"$filename\""); 
    header("Content-Type: application/vnd.ms-excel");
    include("conexion.php");
    $con=conectar();
    $query = 'SELECT l.Titulo AS libro, l.Autor AS autor, l.ClasificacionID AS clasificacion, l.CodigoClasificacion AS clasificacion_completa, CONCAT(u.Nombre, " ", u.Apellido) AS nombre, u.Cedula AS cedula, p.Obervacion AS observacion, p.fecha_prestamo AS fecha_prestamo, p.fecha_limite AS fecha_limite, IF(devuelto =1,"si", "no") AS devuelto, p.fecha_entregado AS fecha_entregado    FROM prestamos p JOIN libros l ON l.id = p.LibrosID JOIN usuarios u ON u.Cedula = p.UsuariosID;';
    $stmt = $con->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $flag = false;
while ($row = $result->fetch_assoc()) {
    if (!$flag) {
        // display field/column names as first row
        echo implode("\t", array_keys($row)) . "\r\n";
        $flag = true;
    }
    echo implode("\t", array_values($row)) . "\r\n";
}
}





?>