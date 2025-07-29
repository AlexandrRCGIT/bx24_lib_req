<?php

namespace Bx24\Traits;

trait bitrix_processesTrait
{
    /**
     * Создать элемент списка бизнесс-процесса
     * 
     * @param array $params
     *  - IBLOCK_TYPE_ID - Идентификатор типа инфоблока (обязательно): lists, bitrix_processes, lists_socnet
     *  - IBLOCK_ID - Обязательно (ID бизнесс-процесса)
     *  - ELEMENT_CODE - Обязательное (Уникальное значение)
     *  - FIELDS - [ ] Массив
     * @return array Создать элемент бизнесс-процесса
     */
    public function addElemInBP(int $params): array
    {
        return $this->callMethod('lists.element.add', $params);
    }

    /**
     * Удалить элемент универсального списка
     * 
     * @param array $params
     *  - IBLOCK_TYPE_ID - Идентификатор типа инфоблока (обязательно): lists, bitrix_processes, lists_socnet
     *  - IBLOCK_ID - Обязательно (ID бизнесс-процесса)
     *  - ELEMENT_CODE - Обязательное (Уникальное значение)
     * @return array Результат удаления
     */
    public function deleteElemInBP(int $params): array
    {
        return $this->callMethod('lists.element.delete', $params);
    }

    /**
     * Удалить элемент универсального списка
     * 
     * @param array $params
     *  - IBLOCK_TYPE_ID - Идентификатор типа инфоблока (обязательно): lists, bitrix_processes, lists_socnet
     *  - IBLOCK_ID - Обязательно (ID бизнесс-процесса)
     *  - ELEMENT_ORDER - Сортировка. Массив полей элементов информационного блока. Направление сортировки: asc (по возрастания) или desc (по убыванию)
     *  - FILTER - [ ] - Фильтрация элементов
     *  - SELECT - [ ] - Массив содержит список полей, которые необходимо выбрать.
     * @return array Массив из элементов процесса
     */
    public function getElemsBatchBP(array $params, int $batchSize = 50): array
    {
        $firstResult = $this->callMethod('lists.element.get', $params);
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
            $batchParams['cmd'][] = 'lists.element.get?' . http_build_query(
                array_merge($params, ['start' => $start])
            );

            if (count($batchParams['cmd']) >= 50 || ($start + $batchSize) >= $total) {
                $batchResult = $this->callMethod('batch', $batchParams);

                // Обрабатываем результаты batch-запроса
                foreach ($batchResult['result']['result'] as $batchItems) {
                    array_push($allElems, ...$batchItems);
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
}
