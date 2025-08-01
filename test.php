<?php

require_once '../bx24_req/autoload.php';

use Bx24\Bx24Client;

$bx24 = new Bx24Client('');


$params = [
    'filter' => [
        '>=DATE_CREATE' => '2023-12-01' . "00:00:00",
        '<=DATE_CREATE' => '2025-06-30' .  "23:59:59",
    ],
    'select' => [
        'ID',
        'DATE_CREATE'
    ]
];
// Получение списка элементов бизнес процесса
$lead = $bx24->deleteLeadListBatch($params);
print_r($lead);
