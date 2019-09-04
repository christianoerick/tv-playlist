<?php
require(__DIR__.'/lib/Tv.class.php');

$tv = new Tv;
$status = $tv->converteArquivo('arquivos/lista.m3u', 'arquivos/lista.json');
print_r($status);