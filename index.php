<?php
include_once __DIR__ . "/autoload.php";
use Nucleo\Usuarios;
$usuario = new Usuarios();
session_start();
if (isset($_SESSION['usuario'])) {
    if ($usuario->buscar_usuario($_SESSION['usuario'])) {
        header("Location:/dashboard.php");
        exit;
    }else {
        session_destroy();
    }

}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investimentos</title>
</head>

<body>
    <main>
        <article>
            <section>
                <h1>Logar</h1>
                <div>
                    <form action="logar.php" method="post">
                        <label for="usuario">Usuário</label>
                        <input type="text" id="usuario" name="usuario" placeholder="email">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" value="" placeholder="senha">
                        <input type="submit" id="logar" value="logar">
                </div>
            </section>
            <p>Caso ainda não tenha um cadastro; <a href="/cadastrar.php">cadastre-se</a>.</p>
        </article>
    </main>
</body>

</html>