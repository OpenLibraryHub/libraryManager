<?php

include("back/conexion.php");
session_start();

if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {


    $radio = $_POST["confirmar"];
    $cedula = $_POST["cedulaUsuario"];
    $observaciones = $_POST["observaciones"];
    $libroID = $_POST["libroID"];
    
    if($radio=='1'){
        $con=conectar();

        $consulta = $con->prepare("SELECT Cedula FROM usuarios WHERE Cedula = ? OR UsuariosID = ? AND sancionado = 0");

        $consulta->bind_param("ii", $cedula, $cedula);

        $consulta->execute();

        $result = $consulta->get_result();

        $row = $result->fetch_assoc();
        $uId = $row["Cedula"];
        if ($result->num_rows === 0) {
            header("location: usuarios.php");
            exit();
        }
        if (mysqli_connect_errno()) {
          echo "Error al conectar a la base de datos: comuniquese con el ingeniero" . mysqli_connect_error();
          exit();
        }
        $query = "UPDATE libros SET N_Disponible = N_Disponible -1 WHERE id = ? AND N_Disponible > 0 ";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $libroID);
        $stmt->execute();
        if ($query->affected_rows === 0) {
            echo "Hubo un error al Registrar el prestamo porfavor vuelva a intentar";
            header("Location: ../prestamos.php");
            exit();
        }
        $query2 = $con->prepare("INSERT INTO prestamos (LibrosID, UsuariosID, Obervacion) VALUES (?, ?, ?)");
        $query2->bind_param("iis",$libroID, $uId, $observaciones);
        $query2->execute();
        if ($query2->affected_rows > 0) {
            header("location: prestamos.php");
        } else {
            echo "Error con el registro:  de prestamo comunicarse con el Ingeniero" . mysqli_error($con);
            header("location: prestamos.php");

        }
    }else{
        header("location: prestar.php");
    }

mysqli_close($con);

}
?>