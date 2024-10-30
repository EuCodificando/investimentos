<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();

$tipos_investimentos = $ativos->solicitar_tipos_investimento();
$lista_tipos_investimento = [];
// var_dump($tipos_investimentos);exit;
for ($i = 0; $i < count($tipos_investimentos); $i++) {
    $lista_tipos_investimento[$tipos_investimentos[$i]['id']] = $tipos_investimentos[$i]['descricao'];
}
if (!isset($_GET['tipo_investimento'])) {
    session_start();
    $_SESSION['exclusao_tipo_investimento'] = "Preencha/escolha novamente os dados solicite a exclusão.";
    header("Location:/solicitar_exclusao_tipo_investimento.php");
    exit;
}
session_start();
$retorno_exclusao = $ativos->solicitar_exclusao_tipo_investimento($_GET);
if (!is_array($retorno_exclusao)) {
    $_SESSION['exclusao_tipo_investimento'] = "A exclusão para o tipo de investimento {$lista_tipos_investimento[$_GET['tipo_investimento']]}";
} else {
    $_SESSION['exclusao_tipo_investimento'] = $retorno_exclusao['erro'];
}
// var_dump($_SESSION);exit;
header("Location:/solicitar_exclusao_tipo_investimento.php");
exit;
?>