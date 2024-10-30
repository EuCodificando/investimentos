<?php
$mensagem = null;
session_start();
if (isset($_SESSION['cadastro_tipo_investimento'])) {
    $mensagem = $_SESSION['cadastro_tipo_investimento'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar tipo investimento</title>
</head>

<body>
    <article>
        <a href="/">Página inicial</a>
        <a href="/cadastrar_ativo.php">Cadastrar ativo</a>
        <section>
            <form action="/salvar_tipo_investimento.php" method="get">
                <fieldset>
                    <legend>Cadastrar tipo investimento</legend>
                    <fieldset>
                        <legend>Informações sobre o cadastro</legend>
                        <ul>
                            <li>Adicione 1 por vez</li>
                            <li>Evite adicionar ;</li>
                        </ul>
                    </fieldset>
                    <label for="tipo_investimento">Tipo investimento</label>
                    <input type="text" name="tipo_investimento" id="tipo_investimento" required
                        placeholder="CRA's;Commodities">
                    <br>
                    <button type="submit">Salvar</button>
                </fieldset>
                <?php if (!is_null($mensagem)): ?>
                    <fieldset>
                        <legend>Informações de retorno do cadastro</legend>
                        <ul>
                            <li><?=$mensagem?></li>
                        </ul>
                    </fieldset>
                <?php endif ?>
                <?php unset($_SESSION['cadastro_tipo_investimento']) ?>
            </form>
        </section>
    </article>
</body>

</html>