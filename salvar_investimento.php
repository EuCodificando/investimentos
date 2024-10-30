<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
session_start();
if (isset($_GET['ticker'])) {
    $retorno_cadastro = $ativos->cadastrar_investimento($_GET);
    var_dump($retorno_cadastro);
    if ($retorno_cadastro) {
        $_SESSION['cadastro_investimento'] = "O investimento de {$_GET['quantidade']} ação, no valor de R$ {$_GET['valor_cotacao']}, feito para o ativo {$_GET['ticker']}, foi realizado com sucesso.";
    }
    header("Location:/cadastrar_investimento.php");
    exit;
} else {
    $_SESSION['cadastro_investimento'] = "Os dados não estavam completos, preencha o formulário e envie novamente";
    header("Location:/cadastrar_investimento.php");
    exit;
}
?>