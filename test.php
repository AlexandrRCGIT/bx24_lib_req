<?php

require_once 'C:/Users/Genuis/Desktop/разработки/myLibs/bx24_req/autoload.php';

use Bx24\Bx24Client;

$bx24 = new Bx24Client('https://rentasnab.bitrix24.ru/rest/256/68q888xsoht5ypu5/');


$params = [
    'IBLOCK_TYPE_ID' => 'bitrix_processes',
    'IBLOCK_ID' => 76
];
// Получение лида
$lead = $bx24->getElemsBatchBP($params);
print_r($lead);
