<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$setores = $ativos->solicitar_setores();

session_start();
if (isset($_SESSION['cadastro_investimento'])) {
    $retorno_cadastro_investimento = $_SESSION['cadastro_investimento'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de ativos</title>
</head>

<body>
    <article>
<section>
    <a href="/">Pagina inicial</a>
</section>
        <section>
            <form action="salvar_investimento.php" name="cadastro_ativo" method="get">
                <!-- ativo, tipo_aquisicao,data_operacao, quantidade,valor_cotacao -->
                <fieldset>
                    <legend>
                        <h1>Cadastrar investimento:</h1>
                    </legend>
                    <fieldset>
                        <legend>
                            <h2>Dados da operação:</h2>
                        </legend>
                        <div>
                            <label for="tipo_aquisicao_compra">Compra
                                <input type="radio" id="tipo_aquisicao_compra" name="tipo_aquisicao" value="C" checked>
                            </label>
                            <label for="tipo_aquisicao_venda">Venda
                                <input type="radio" id="tipo_aquisicao_venda" name="tipo_aquisicao" value="V">
                            </label>
                        </div>
                        <div>
                            <label for="data">Data da operação</label>
                            <input type="date" id="data" name="data_operacao" required>
                        </div>
                        <div>
                            <label for="quantidade">Quantidade</label>
                            <input type="number" name="quantidade" value="1">
                        </div>
                        <div>
                            <label for="valor_cotacao">Valor da cotação</label>
                            <input type="number" step="0.01" name="valor_cotacao">
                        </div>
                    </fieldset>
                    <div>
                        <button type="submit">Salvar</button>
                    </div>
                </fieldset>
            </form>
        </section>
        <?php
        if (isset($_SESSION['cadastro_investimento'])) {
            echo "<section>";
            echo "$retorno_cadastro_investimento";
            echo "</section>";
        }
        unset($_SESSION['cadastro_investimento']);
        ?>
    </article>
</body>

</html>