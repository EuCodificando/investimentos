<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$ativo = $_GET['ativo'];
session_start();
$_SESSION['ativo'] = $ativo;
$dados_ativo = $ativos->solicitar_lista_posicoes($ativo);
$cotacoes_ativo = $ativos->solicitar_cotacoes($ativo);
var_dump($_GET, $dados_ativo);
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
</body>

</html>