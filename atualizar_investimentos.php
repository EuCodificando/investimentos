<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$retorno_atualizacao = $ativos->solicitar_atualizacao_investimentos();
// var_dump($ativos->solicitar_total_investido());exit;
if ($retorno_atualizacao) {
    header("Location:/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização dos investimentos</title>
</head>

<body>
    <article>
        <section>
            <h1>Erro: <?= $retorno_atualizacao['erro'] ?></h1>
        </section>
        <section>
            <a href="/">Página inicial</a>
        </section>
    </article>
</body>

</html>