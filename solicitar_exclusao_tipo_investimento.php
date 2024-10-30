<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
// $lista_posicoes = $ativos->solicitar_lista_posicoes();
// var_dump($lista_posicoes);exit;
$lista_tipos_investimento = $ativos->solicitar_tipos_investimento();
$mensagem = null;
session_start();
if (isset($_SESSION['exclusao_tipo_investimento'])) {
    if (!is_array($_SESSION['exclusao_tipo_investimento'])) {
        $mensagem = $_SESSION['exclusao_tipo_investimento'];
    } else {
        $mensagem = $_SESSION['exclusao_tipo_investimento']['erro'];
    }
}
// var_dump($mensagem);exit;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar exclusão do tipo de investimento</title>
</head>

<body>
    <article>
        <section>
            <a href="/">Página inicial</a>
            <a href="/cadastrar_ativo.php">Cadastrar ativo</a>
            <form action="/excluir_tipo_investimento.php" method="get">
                <fieldset>
                    <fieldset>
                        <legend>Informações sobre exclusão</legend>
                        <ul>
                            <li>Esta ação não poderá ser revertida</li>
                            <li>Os ativos que tiveram este tipo de investimento associado, perderão essa associação</li>
                            <li>Caso estes ativos,que serão listados abaixo, não recebam novamente um tipo de
                                investimento associado a eles, alguns relatórios não o exibirão</li>
                        </ul>
                    </fieldset>
                    <fieldset>
                        <label for="tipo_investimento">Selecione o tipo de investimento</label>
                        <select name="tipo_investimento" id="tipo_investimento">
                            <?php for ($i = 0; $i < count($lista_tipos_investimento); $i++): ?>
                                <option value="<?= $lista_tipos_investimento[$i]['id'] ?>">
                                    <?= $lista_tipos_investimento[$i]['descricao'] ?>
                                </option>
                            <?php endfor ?>
                        </select>
                        <button type="submit">Excluir</button>
                    </fieldset>
                </fieldset>
                <?php if (!is_null($mensagem)): ?>
                    <fieldset>
                        <legend>Informações de retorno para exclusão do tipo de investimento</legend>
                        <ul>
                            <li><?= $mensagem ?></li>
                        </ul>
                    </fieldset>
                <?php endif ?>
                <?php unset($_SESSION['exclusao_tipo_investimento']); ?>
            </form>
        </section>
    </article>
</body>

</html>