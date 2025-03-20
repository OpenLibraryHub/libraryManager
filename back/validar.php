<?php
include("conexion.php");

class Registro{
    public $email;
    public $password;
    public function __construct($email, $password){
        $this->email=$email;
        $this->password=$password;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
$con=conectar();
if (mysqli_connect_errno()) {
    echo "Error al conectar a la base de datos comuniquese con el Ingeniero" . mysqli_connect_error();
  exit();
}

$registro=new Registro($_POST['email'], $_POST['password']);

$correo = mysqli_real_escape_string($con, $registro->email); 
$pasw = mysqli_real_escape_string($con, $registro->password); 

$consulta = $con->prepare("SELECT * FROM librarians WHERE email=? AND password=?");

$consulta->bind_param("ss" , $correo, $pasw );

$consulta->execute();

$result = $consulta->get_result();

$con->close();

if ($result->num_rows === 1) {

    $row = $result->fetch_assoc();
    session_start();

    $_SESSION['idUsuario'] = $row['id'];
    $_SESSION['correo'] = $row['email'];
    $_SESSION['nombre'] = $row['first_name'];
    $_SESSION['apellido'] = $row['paternal_last_name'];
    $_SESSION['password'] = $row['password'];
    header("Location: ../usuarios.php"); 

    exit();
} else {
    header("Location: ../index.html");
}
} else {
    header("Location: ../index.html");

}   

?>