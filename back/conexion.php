<?php
    function conectar(){
        $env = parse_ini_file(__DIR__ . '/../.env');
        $host=$env["HOST"];
        $user=$env["USER"];
        $pass=$env["PASSWORD"];
        $cont=$env["DATABASE"];

        $con=mysqli_connect($host,$user,$pass);

        mysqli_select_db($con,$cont);

        return $con;
    }
?>