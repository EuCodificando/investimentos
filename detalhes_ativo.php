<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
use Nucleo\BaseDados;
$iBanco = new BaseDados();
$iAtivo = new Ativos();
$ativo = $_GET['ativo'];
session_start();
$_SESSION['ativo'] = $ativo;
$dados_ativo = $iAtivo->solicitar_lista_ativos($ativo)[0];
var_dump($_GET, $dados_ativo);
$cotacoes_ativo = $iAtivo->solicitar_cotacoes($dados_ativo);
// var_dump($cotacoes_ativo);exit;
$retorno_data = $iAtivo->solicitar_data_atualizacao_cotacoes();
if (is_array($retorno_data) && in_array('erro', array_keys($retorno_data))) {
    $retorno_ultima_cotacao = $iBanco->obter_ultima_cotacao($dados_ativo)[0];
    // var_dump($retorno_ultima_cotacao);
    $retorno_atualizacao = $iBanco->atualizar_data_cotacoes($retorno_ultima_cotacao['data_cotacao']);
    if ($retorno_atualizacao) {
        $retorno_data = $iBanco->obter_data_atualizacao_cotacoes()['data_atualizacao_cotacoes'];
        var_dump($retorno_data);
    }
    // var_dump($retorno_atualizacao);exit;
}
// var_dump($retorno_data);exit;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do ativo <?= $ativo ?></title>
</head>

<body>
    <section>
        <a href="/editar_ativo.php">Editar ativo</a>
    </section>
    <section>
        <p>Data da última cotação: <?= $retorno_data['data_atualizacao_cotacoes'] ?></p>
    </section>
</body>

</html>