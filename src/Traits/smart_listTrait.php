<?php

namespace Bx24\Traits;

trait smart_listTrait
{
    public function getSmartListBatch(array $params, int $batchSize = 50): array
    {
        $firstResult = $this->callMethod('crm.item.list', $params);
        $total = (int)$firstResult['total'];
        if ($total <= $batchSize) {
            return [
                'total' => $total,
                'req' => $firstResult['result'],
                'sumReq' => count($firstResult['result'])
            ];
        }

        $allElems = [];
        $batchParams = ['halt' => 0, 'cmd' => []];

        for ($start = 0; $start < $total; $start += $batchSize) {
            $batchParams['cmd'][] = 'crm.item.list?' . http_build_query(
                array_merge($params, ['start' => $start])
            );

            if (count($batchParams['cmd']) >= 50 || ($start + $batchSize) >= $total) {
                $batchResult = $this->callMethod('batch', $batchParams);

                // Обрабатываем результаты batch-запроса
                foreach ($batchResult['result']['result'] as $batchItems) {
                    array_push($allElems, ...$batchItems['items']);
                }

                $batchParams['cmd'] = []; // Сбрасываем команды для следующего batch
            }
        }

        return [
            'total' => $total,
            'req' => $allElems,
            'sumReq' => count($allElems)
        ];
    }
    //Нужно добавить описание 
    public function updateSmartListBatch(array $params, int $batchSize = 50): array
    {
        $total = (int)count($params);

        $allElems = [];
        $batchParams = ['halt' => 0, 'cmd' => []];
        foreach ($params as $key => $item) {
            $batchParams['cmd'][] = 'crm.item.update?' . http_build_query([
                'entityTypeId' => $item['entityTypeId'],
                'id' => $item['id'],
                'fields' => $item['fields'],
                'useOriginalUfNames' => 'Y'
            ]);
            // Если набрали 50 команд или это последний элемент
            if (count($batchParams['cmd']) >= $batchSize || $key === array_key_last($params)) {
                $batchResult = $this->callMethod('batch', $batchParams);
                $allElems[] = $batchResult;
                $batchParams['cmd'] = []; // Сбрасываем команды
            }
        }

        return [
            'total' => $total,
            'req' => $allElems,
            'sumReq' => 0
        ];
    }
}
