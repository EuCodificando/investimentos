<?php
include_once __DIR__."/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
if (!isset($_GET['nome_ativo'])) {
    session_start();
    $_SESSION['retorno_salvar_edicao_ativo'] = "Algo deu errado, preencha novamente o formulário.";
    header("Location:/editar_ativo.php");
    exit;
}
$retorno_editar = $ativos->editar_ativo($_GET);
?>