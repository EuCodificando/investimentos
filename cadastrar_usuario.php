<?php
include_once __DIR__ . "/autoload.php";

use Nucleo\Usuarios;
$retorno_tentativa_cadastro = '';
if (isset($_POST['usuario'])) {
    var_dump($_POST);
    $usuario = new Usuarios();
    $retorno_tentativa_cadastro = $usuario->cadastrar_usuario($_POST);
    var_dump($retorno_tentativa_cadastro);
}
?>

<main>
    <article>
        <section>
            <h1>Cadastrar</h1>
            <form action="cadastrar_usuario.php" method="post">
                <label for="usuario">Digite um email válido:</label>
                <input type="text" name="usuario" required="true" autocomplete="email">
                <label for="usuario">Digite uma senha:</label>
                <input type="password" name="senha" required="true">
                <button type="submit">Enviar</button>
            </form>
        </section>
        <?= ($retorno_tentativa_cadastro) ?>
        <p><a href="/index.php">Página inicial</a></p>
    </article>
</main>