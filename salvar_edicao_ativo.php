<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
if (!isset($_GET['nome_ativo'])) {
    session_start();
    $_SESSION['retorno_salvar_edicao_ativo'] = "Algo deu errado, preencha novamente o formulário.";
    header("Location:/editar_ativo.php");
    exit;
}
$retorno_editar = $ativos->editar_ativo($_GET);
// var_dump($retorno_editar,$_GET);exit;
session_start();
$_SESSION['ativo'] = $_GET['nome_ativo'];
if ($retorno_editar) {
    $_SESSION['retorno_edicao_ativo'] = "A edição para o ativo {$_GET['nome_ativo']} foi realizada com sucesso.";
    header("Location:/detalhes_ativo.php");
    exit;
} else {
    // var_dump('aqui');exit;
    $_SESSION['retorno_edicao_ativo'] = "Nenhuma alteração foi feita para o ativo {$_GET['nome_ativo']}.";
    header("Location:/detalhes_ativo.php");
    exit;
}
?>