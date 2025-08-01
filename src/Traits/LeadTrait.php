<?php

namespace Bx24\Traits;

trait LeadTrait
{
    /**
     * Получает лид по ID
     */
    public function getLead(int $id): array
    {
        return $this->callMethod('crm.lead.get', ['id' => $id]);
    }

    /**
     * Получает список лидов
     */
    public function getLeadList(array $params): array
    {
        return $this->callMethod('crm.lead.list', $params);
    }


    /**
     * Удаление лида
     * @param int $id ID лида
     * @return array Результат удаления лида
     */
    public function deleteLead(int $id): array
    {
        if (!empty($id)) {
            return $this->callMethod('crm.lead.delete', ['id' => $id]);
        } else {
            return ['error' => 'empty id'];
        }
    }

    public function getLeadListBatch(array $params, int $batchSize = 50): array
    {
        $firstResult = $this->callMethod('crm.lead.list', $params);
        $total = (int)$firstResult['total'];
        if ($total <= $batchSize) {
            return [
                'total' => $total,
                'req' => $firstResult['result'],
                'sumReq' => count($firstResult['result'])
            ];
        }

        $allLeads = [];
        $batchParams = ['halt' => 0, 'cmd' => []];

        for ($start = 0; $start < $total; $start += $batchSize) {
            $batchParams['cmd'][] = 'crm.lead.list?' . http_build_query(
                array_merge($params, ['start' => $start])
            );

            if (count($batchParams['cmd']) >= 50 || ($start + $batchSize) >= $total) {
                $batchResult = $this->callMethod('batch', $batchParams);

                // Обрабатываем результаты batch-запроса
                foreach ($batchResult['result']['result'] as $batchItems) {
                    array_push($allLeads, ...$batchItems);
                }

                $batchParams['cmd'] = []; // Сбрасываем команды для следующего batch
            }
        }
        return [
            'total' => $total,
            'req' => $allLeads,
            'sumReq' => count($allLeads)
        ];
    }

    public function deleteLeadListBatch(array $params, int $batchSize = 50): array
    {
        $firstResult = $this->callMethod('crm.lead.list', $params);
        $total = (int)$firstResult['total'];
        if ($total <= $batchSize) {
            return [
                'total' => $total,
                'req' => $firstResult['result'],
                'sumReq' => count($firstResult['result'])
            ];
        }

        $allLeads = [];
        $batchParams = ['halt' => 0, 'cmd' => []];

        for ($start = 0; $start < $total; $start += $batchSize) {
            $batchParams['cmd'][] = 'crm.lead.list?' . http_build_query(
                array_merge($params, ['start' => $start])
            );

            if (count($batchParams['cmd']) >= 50 || ($start + $batchSize) >= $total) {
                $batchResult = $this->callMethod('batch', $batchParams);

                // Обрабатываем результаты batch-запроса
                foreach ($batchResult['result']['result'] as $batchItems) {
                    array_push($allLeads, ...$batchItems);
                }

                $batchParams['cmd'] = []; // Сбрасываем команды для следующего batch
            }
        }
        $batchParamsDelete = ['halt' => 0, 'cmd' => []];
        $delLeads = [];
        foreach ($allLeads as $key => $item) {
            $batchParamsDelete['cmd'][] = 'crm.lead.delete?' . http_build_query(
                [
                    'id' => $item['ID']
                ]
            );
            if (count($batchParamsDelete['cmd']) >= 50 || $key === array_key_last($allLeads)) {
                $batchResult = $this->callMethod('batch', $batchParamsDelete);
                array_push($delLeads, ...$batchResult['result']['result']);
                // Обрабатываем результаты batch-запроса
                $batchParamsDelete['cmd'] = []; // Сбрасываем команды для следующего batch
            }
        }
        return [
            'total' => count($allLeads),
            'req' => $delLeads,
            'sumReq' => count($delLeads)
        ];
    }
    // Другие методы для работы с лидами
}
