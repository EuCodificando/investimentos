<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$setores = $ativos->solicitar_setores();
$lista_setores = [];
for ($i = 0; $i < count($setores); $i++) {
    $lista_setores[$setores[$i]['id']] = $setores[$i]['descricao'];
}
$tipos_investimentos = $ativos->solicitar_tipos_investimento();
$lista_tipos_investimento = [];
for ($i = 0; $i < count($tipos_investimentos); $i++) {
    $lista_tipos_investimento[$tipos_investimentos[$i]['id']] = $tipos_investimentos[$i]['descricao'];
}
session_start();
// var_dump($_GET);exit;
if (isset($_GET['ticker'])) {
    $retorno_cadastro = $ativos->cadastrar_ativo($_GET);
    // var_dump($retorno_cadastro);exit;
    if ($retorno_cadastro || (is_array($retorno_cadastro) && !in_array('erro', array_keys($retorno_cadastro)))) {
        $_SESSION['cadastro_ativo'] = "O ativo {$_GET['ticker']}, um(a) {$lista_tipos_investimento[$_GET['tipo_investimento']]}, ativo com base de {$_GET['nacionalidade']}, foi cadastrado no setor de {$lista_setores[$_GET['setor']]} com sucesso.";
    } else {
        $_SESSION['cadastro_ativo'] = $retorno_cadastro['erro'];
    }
    header("Location:/cadastrar_ativo.php");
    exit;
} else {
    $_SESSION['cadastro_ativo'] = "Os dados não estavam completos, preencha o formulário e envie novamente";
    header("Location:/cadastrar_ativo.php");
    exit;
}
?>