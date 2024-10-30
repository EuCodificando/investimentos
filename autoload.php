<?php
spl_autoload_register('config');
function config($arquivo)
{
    $diretorios = ['/', '/Nucleo', '/Classes'];
    foreach ($diretorios as $key => $value) {
        $caminho = __DIR__ . "$value/$arquivo.php";
        if (file_exists($caminho)) {
            include_once $caminho;
            // var_dump("Arquivo do caminho; $caminho foi incluído");
            break;
        } else {
            // var_dump("Arquivo $caminho não foi encontrado.");
        }
    }
}