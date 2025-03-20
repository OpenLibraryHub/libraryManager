<?php
session_start();
if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
}
    header("Access-Control-Allow-Origin: *");
    // Conexión a la base de datos
    include("back/conexion.php");
    $conexion=conectar();

    // Consulta
    $consulta = "SELECT * FROM usuarios";
    $resultado = mysqli_query($conexion, $consulta);

    // Almacenamiento de datos en una array
    $datos = array();
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $lista=array(
            'Nombre'=>$fila['Nombre'],
            'Apellido'=>$fila['Apellido'],
            'Cedula'=>$fila['Cedula'],
            'UsuariosID'=>$fila['UsuariosID'],
            'numero'=>$fila['numero']
        );
        
        $datos[] = $lista;
    }
    $json = json_encode($datos);
    echo $json;

?>