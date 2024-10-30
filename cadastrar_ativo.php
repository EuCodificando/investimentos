<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$setores = $ativos->solicitar_setores();
$tipos_investimentos = $ativos->solicitar_tipos_investimento();
// var_dump($tipos_investimentos);

session_start();
if (isset($_SESSION['cadastro_ativo'])) {
    $retorno_cadastro_ativo = $_SESSION['cadastro_ativo'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar ativo</title>
</head>

<body>
    <section>
        <a href="/">Página inicial</a>
    </section>
    <section>
        <form action="salvar_ativo.php" name="cadastro_ativo" method="get">
            <fieldset>
                <legend>
                    <h2>Dados do ativo:</h2>
                </legend>
                <div>
                    <label for="ticker">Ativo</label>
                    <input type="text" id="ticker" name="ticker" placeholder="PETR4F" required>
                </div>
                <div>
                    <label for="tipo_investimento">Tipo investimento</label>
                    <select name="tipo_investimento" id="tipo_investimento">Tipo
                        <?php
                        for ($i = 0; $i < count($tipos_investimentos); $i++) {
                            echo "<option value='{$tipos_investimentos[$i]['id']}'>{$tipos_investimentos[$i]['descricao']}</option>";
                        }
                        ?>
                    </select>
                    <p>
                    <a href="cadastrar_tipo_investimento.php">Cadastrar novo tipo de investimento</a>
                    <a href="solicitar_exclusao_tipo_investimento.php">Excluir tipo investimento</a>
                    </p>
                </div>
                <div>
                    <label for="setor">Setor</label>
                    <select name="setor" id="setor">Setor
                        <?php
                        for ($i = 0; $i < count($setores); $i++) {
                            echo "<option value='{$setores[$i]['id']}'>{$setores[$i]['descricao']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="nacionalidade">Investimento nacional</label>
                    <input type="radio" id="nacionalidade" name="nacionalidade" value="Investimento nacional" checked>
                    <label for="nacionalidade">Investimento estrangeiro</label>
                    <input type="radio" id="nacionalidade" name="nacionalidade" value="Investimento estrangeiro">
                </div>
            </fieldset>
            <div>
                <button type="submit">Salvar</button>
            </div>
        </form>
    </section>
    <?php
    if (isset($_SESSION['cadastro_ativo'])) {
        echo "<section>";
        echo "$retorno_cadastro_ativo";
        echo "</section>";
    }
    unset($_SESSION['cadastro_ativo']);
    ?>
</body>

</html>