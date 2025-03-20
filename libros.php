<?php
session_start();
if(!isset($_SESSION['correo'])){
    header('Location: ../index.html');
    exit;
}
    header("Access-Control-Allow-Origin: *");
    include("back/conexion.php");
    $conexion=conectar();

    $consulta = "SELECT * FROM libros";
    $resultado = mysqli_query($conexion, $consulta);

    $datos = array();
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $lista=array(
            'id'=>$fila['id'],
            'LibrosID'=>$fila['LibrosID'],
            'Titulo'=>$fila['Titulo'],
            'Autor'=>$fila['Autor'],
            'ClasificacionID'=>$fila['ClasificacionID'],
            'CodigoClasificacion'=>$fila['CodigoClasificacion'],
            'N_Ejemplares'=>$fila['N_Ejemplares'],
            'OrigenID'=>$fila['OrigenID'],
            'N_Disponible'=>$fila['N_Disponible'],
            'EtiquetaID'=>$fila['EtiquetaID'],
            'BibliotecaID'=>$fila['BibliotecaID'],
            'SalaID'=>$fila['SalaID'],
            'Observacion'=>$fila['Observacion']
        );
        
        $datos[] = $lista;
    }
    $json = json_encode($datos);
    echo $json;

?>