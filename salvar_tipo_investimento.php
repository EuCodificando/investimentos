<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
if ($_GET['tipo_investimento']) {
    $retorno_cadastro = $ativos->cadastrar_tipo_investimento($_GET);
    // var_dump($retorno_cadastro);exit;
    if (!is_array($retorno_cadastro)) {
        session_start();
        $_SESSION['cadastro_tipo_investimento'] = "O tipo de investimento {$_GET['tipo_investimento']} agora está disponível na lista.";
    } else {
        session_start();
        $_SESSION['cadastro_tipo_investimento'] = $retorno_cadastro['erro'];
    }
    header("Location:/cadastrar_tipo_investimento.php");
    exit;
} else {
    header("Location:/cadastrar_ativo.php");
    exit;
}
?>