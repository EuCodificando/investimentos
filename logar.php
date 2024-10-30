<?php
include_once __DIR__ . "/autoload.php";

use Nucleo\Usuarios;

$usuario = new Usuarios();
$retorno_tentativa_login = $usuario->logar($_POST);
// var_dump($retorno_tentativa_login);exit;
if (!$retorno_tentativa_login) {
    header("Location:/");
    exit;
} else {
    header("Location:dashboard.php");
    exit;
}
?>