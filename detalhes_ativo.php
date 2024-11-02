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