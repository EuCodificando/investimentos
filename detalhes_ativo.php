<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$ativo = $_GET['ativo'];
$dados_ativo = $ativos->solicitar_lista_posicoes($_GET['ativo']);
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

</body>

</html>