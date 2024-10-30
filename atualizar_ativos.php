<?php
include_once __DIR__."/autoload.php";
use Nucleo\Ativos;
$ativos = new Ativos();
$ativos->solicitar_atualizacao_lista_ativos();
header("Location:/dashboard.php");
exit;