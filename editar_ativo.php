<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
session_start();
if (isset($_SESSION['ativo'])) {
    $ativo = $_SESSION['ativo'];

    $dados_ativo = $ativos->solicitar_lista_ativos($ativo);
    $setores = $ativos->solicitar_setores();
    $lista_setores = [];
    for ($i = 0; $i < count($setores); $i++) {
        $lista_setores[$setores[$i]['id']] = $setores[$i]['descricao'];
    }
    $tipos_investimentos = $ativos->solicitar_tipos_investimento();
    $lista_tipos_investimento = [];
    for ($i = 0; $i < count($tipos_investimentos); $i++) {
        $lista_tipos_investimento[$tipos_investimentos[$i]['id']] = $tipos_investimentos[$i]['descricao'];
    }
    // var_dump($dados_ativo, $lista_setores, $lista_tipos_investimento);//exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <section>
        <a href="/">Página inicial</a>
        <a href="/detalhes_ativo.php">Detalhes <?= $ativo ?></a>
    </section>
    <section>
        <form action="/salvar_edicao_ativo.php" method="get">
            <fieldset>
                <legend>Edição do ativo</legend>
                <div>
                    <fieldset>
                        <?php
                        $temp_tipo = !in_array($dados_ativo[0]['tipo_investimento_id'], array_keys($lista_tipos_investimento))
                            ? 'Não encontrado'
                            : $lista_tipos_investimento[$dados_ativo[0]['tipo_investimento_id']];
                        ?>
                        <legend>Dados atuais do ativo</legend>
                        <ul>
                            <li><?= $ativo ?></li>
                            <li><?= $lista_setores[$dados_ativo[0]['setor_id']] ?></li>
                            <li><?= $temp_tipo ?></li>
                            <li><?= $dados_ativo[0]['nacionalidade'] ?></li>
                        </ul>
                    </fieldset>
                </div>
                <input hidden type="text" name="id" value="<?= $dados_ativo[0]['id'] ?>">
                <label for="nome_ativo">Nome do ativo:</label>
                <input type="text" name="nome_ativo" value="<?= $ativo ?>" required>
                <div>
                    <label for="setor">Setor</label>
                    <select name="setor" id="setor">
                        <?php foreach ($lista_setores as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach ?>
                        <option value="<?= $dados_ativo[0]['setor_id'] ?>"
                            selected="<?= $lista_setores[$dados_ativo[0]['setor_id']] ?>">
                            <?= $lista_setores[$dados_ativo[0]['setor_id']] ?>
                        </option>
                    </select>
                </div>
                <label for="tipo_investimento">Tipo de investimento</label>
                <select name="tipo_investimento" id="tipo_investimento">
                    <option value="<?= $dados_ativo[0]['tipo_investimento_id'] ?>" selected="<?= $temp_tipo ?>">
                        <?= $temp_tipo ?>
                    </option>
                    <?php foreach ($lista_tipos_investimento as $key => $value): ?>
                        <option value="<?= $key ?>"><?= $value ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <div>
                    <label for="nacionalidade">Investimento nacional</label>
                    <input type="radio" name="nacionalidade" id="nacional" value="Investimento nacional" checked>
                    <label for="nacionalidade">Investimento estrangeiro</label>
                    <input type="radio" name="nacionalidade" id="nacional" value="Investimento estrangeiro">
                </div>
                <div>
                    <button type="submit">Salvar</button>
                </div>
            </fieldset>
        </form>
    </section>
</body>

</html>