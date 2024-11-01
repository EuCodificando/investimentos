<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
use Nucleo\Usuarios;
$usuario = new Usuarios();
session_start();

if (!$_SESSION['usuario']) {
    header("Location:/");
    exit;
} else {
    $veriricar_existencia_usuario = $usuario->buscar_usuario($_SESSION['usuario']);
    if (!$veriricar_existencia_usuario) {
        session_destroy();
        header("Location:/");
        exit;
    }
}
$usuario = $_SESSION['usuario'];
$ativos = new Ativos();
$lista_ativos = $ativos->solicitar_lista_ativos();
// var_dump($lista_ativos);exit;

$lista_posicoes = $ativos->solicitar_lista_posicoes();
// var_dump($lista_posicoes);//exit;
// var_dump($posicoes);//exit;

$posicoes_abertas = $ativos->obter_posicoes_abertas($lista_posicoes);
$qntd_posicoes_abertas = count($posicoes_abertas);
// var_dump($posicoes_abertas);exit;
$posicoes_fechadas = $ativos->obter_posicoes_fechadas($lista_posicoes);
$qntd_posicoes_fechadas = count($posicoes_fechadas);
// var_dump($posicoes_abertas, $posicoes_fechadas);//exit;



$dados_investimentos = $ativos->solicitar_total_investido($lista_ativos, $lista_posicoes);
$posicoes = [];
foreach ($dados_investimentos as $key => $value) {
    $posicoes[$key] = $dados_investimentos[$key]['quantidade_total'];
}
$total_contratos = 0;
$total_investimento = 0.0;
$lp_posicao = 0.0;
$lp_operacao = 0.0;
$total_lucro = 0.0;
foreach ($dados_investimentos as $key => $value) {
    $total_contratos += $dados_investimentos[$key]['quantidade_total'];
    $total_investimento += $dados_investimentos[$key]['total_investido'];
    $lp_posicao += $dados_investimentos[$key]['lp_posicao'];
    $lp_operacao += $dados_investimentos[$key]['lp_operacao'];
    $total_lucro += $dados_investimentos[$key]['lp_posicao'] + $dados_investimentos[$key]['lp_operacao'];
}
// var_dump($dados_investimentos);//exit;



// var_dump($qntd_posicoes_abertas, $qntd_posicoes_fechadas);exit;
$total_investido_posicoes_abertas = $ativos->obter_total_investido_posicoes_abertas(
    $lista_posicoes,
    $posicoes_abertas
);
$total_investido_posicoes_fechadas = $ativos->obter_total_investido_posicoes_fechadas(
    $lista_posicoes,
    $posicoes_fechadas
);
// var_dump($total_investido_posicoes_abertas, $total_investido_posicoes_fechadas);

// var_dump($dados_investimentos);//exit;

$ativos_setor = $ativos->solicitar_ativos_setor();


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/echarts/echarts.js"></script>
    <title>Dashboard <?= $usuario ?></title>
</head>

<body>
    <article>
        <section>
            <h1>Dashboard <?= $usuario ?></h1>
        </section>
        <section>
            <div>
                <a href="/cadastrar_ativo.php">Cadastrar ativo</a>
            </div>
        </section>
    </article>
    <style>
        .teste {
            text-align: right;
        }
    </style>
    <article>
        <section>
            <table class="teste">
                <thead>
                    <tr>
                        <th>Ativo</th>
                        <th>Contratos</th>
                        <th>Preço médio</th>
                        <th>Investimento</th>
                        <th>L/P da posição</th>
                        <th>L/P de operações</th>
                        <th>Saldo L/P</th>
                    </tr>
                </thead>
                <tbody>
                    <form action="detalhes_ativo.php" method="get">
                        <?php foreach ($posicoes as $key => $value): ?>
                            <tr>
                                <th scope='row'><button type="submit" name="ativo" value="<?= $key ?>"><?= $key ?></th>
                                <td><?= $value ?></td>
                                <td> <?= number_format($dados_investimentos[$key]['preco_medio'], 2, ',', '.') ?></td>
                                <td> <?= number_format($dados_investimentos[$key]['total_investido'], 2, ',', '.') ?></td>
                                <td> <?= number_format($dados_investimentos[$key]['lp_posicao'], 2, ',', '.') ?></td>
                                <td> <?= number_format($dados_investimentos[$key]['lp_operacao'], 2, ',', '.') ?></td>
                                <td>
                                    <?= number_format($dados_investimentos[$key]['lp_posicao'] + $dados_investimentos[$key]['lp_operacao'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <tr>
                            <th scope="row">Total</th>
                            <td colspan='1'><?= $total_contratos ?></td>
                            <td></td>
                            <td> <?= number_format($total_investimento, 2, ',', '.') ?>
                            </td>
                            <td> <?= number_format($lp_posicao, 2, ',', '.') ?>
                            </td>
                            <td> <?= number_format($lp_operacao, 2, ',', '.') ?>
                            </td>
                            <td> <?= number_format($total_lucro, 2, ',', '.') ?>
                            </td>
                        </tr>
                    </form>
                </tbody>
            </table>
        </section>
        <section>
            <table class="teste">
                <thead>
                    <th>Setor</th>
                    <th>Qntd</th>
                    <th>Posicionado</th>
                    <th>Operação encerrada</th>
                </thead>
                <tbody>
                    <?php foreach ($ativos_setor as $key => $value): ?>
                        <tr>
                            <td><?= $key ?></td>
                            <td><?= count($value) ?></td>
                            <td>5</td>
                            <td>25</td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </section>
        <section>
            <!-- ECHARTS - GRÁFICO PIZZA SETORES -->
            <div id="main" style="width: 600px; height:400px"></div>
            <?php
                $eixo_x = implode("','", array_keys($ativos_setor));
                $dados_eixo_y = [];
                foreach ($ativos_setor as $value) {
                    $dados_eixo_y[] = count($value);
                }

                $eixo_y = implode(',', $dados_eixo_y);
                var_dump($eixo_y);
                ?>
            <script type="text/javascript">
                //inicializando
                var myChart = echarts.init(document.getElementById('main'));
                //definindo as opções
                var option = {
                    title: {
                        text: 'Gráfico de exemplo'
                    },
                    tooltip: {},
                    legend: {
                        data: ['Ativos']
                    },
                    xAxis: {
                        data: ['<?= $eixo_x ?>']
                    },
                    yAxis: {},
                    series: [
                        {
                            name: 'setores',
                            type: 'pie',
                            data: [<?= $eixo_y ?>]
                        }
                    ]
                };
                // Plotar gráfico
                myChart.setOption(option);
            </script>
        </section>
    </article>
</body>

</html>