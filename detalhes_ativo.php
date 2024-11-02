<?php
date_default_timezone_set("America/Sao_Paulo");
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
use Nucleo\BaseDados;
$iBanco = new BaseDados();
$iAtivo = new Ativos();
session_start();
if (isset($_GET['ativo'])) {
    $ativo = $_GET['ativo'];
    $_SESSION['ativo'] = $ativo;
} else if (isset($_SESSION['ativo'])) {
    $ativo = $_SESSION['ativo'];
}
$info = null;
if (isset($_SESSION['retorno_edicao_ativo'])) {
    $info = $_SESSION['retorno_edicao_ativo'];
}
var_dump($_SESSION);
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
        $retorno_data = $iBanco->obter_data_atualizacao_cotacoes();
        var_dump($retorno_data);
    }

    // var_dump($retorno_atualizacao);exit;
}
$data_atualizacao = new \DateTime($retorno_data['data_atualizacao_cotacoes']);
$eixo_x = [];
$eixo_y = [];
for ($i = 252; $i > 0; $i--) {
    $eixo_x[] = date_format(new \DateTime($cotacoes_ativo[$i]['data_cotacao']), 'd');
    $eixo_y[] = $cotacoes_ativo[$i]['fechamento'];
}

$x = implode("','", $eixo_x);
$y = implode(',', $eixo_y);

var_dump(count($eixo_x), $x, count($eixo_y), $y);
$data_atual = new \DateTime('now');

$intervalo = new \DateInterval('P1DT0M');

$intervalo = $data_atualizacao->diff($data_atual);
$libera_atualizacao_cotacao = $intervalo->days >= 1;
// var_dump($data);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/echarts/echarts.js"></script>
    <title>Detalhes do ativo <?= $ativo ?></title>
</head>

<body>
    <section>
        <a href="/">Página inicial</a>
        <a href="/editar_ativo.php">Editar ativo</a>
    </section>
    <?php if (!is_null($info)): ?>
        <section>
            <fieldset>
                <legend>Info</legend>
                <p><?= $info ?></p>
            </fieldset>
        </section>
        <?php unset($_SESSION['retorno_edicao_ativo']) ?>;
    <?php endif ?>
    <section>
        <p>Data da última cotação: <?= date_format($data_atualizacao, 'd/m/Y') ?></p>
        <?php if ($libera_atualizacao_cotacao): ?>
            <a href="/atualizar_cotacoes.php">Atualizar cotacoes</a>
        <?php endif ?>
    </section>
    <section class="grafico">
        <div id="main" style="width:100%;height:400"></div>
        <script type="text/javascript">

            var grafico = echarts.init(document.getElementById('main'));
            window.addEventListener('resize', function () {
                grafico.resize();
            });
            var option;

            option = {
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: ['<?= $x ?>']
                },
                yAxis: {
                    type: 'value'
                },
                tooltip: {
                    trigger: 'axis'
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 0,
                        end: 50
                    },
                    {
                        start: 10,
                        end: 70
                    }
                ],
                series: [
                    {
                        name: 'Preço',
                        data: [<?= $y ?>],
                        type: 'line',
                        // smooth: true
                        areaStyle: {}
                    },
                ]
                
            };
            grafico.setOption(option);
        </script>
    </section>
</body>

</html>