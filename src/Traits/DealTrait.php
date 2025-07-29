<?php

namespace Bx24\Traits;

trait DealTrait
{
    /**
     * Упрощенный метод для получения сделки по ID
     * 
     * @param int $id ID сделки
     * @return array Данные сделки
     */
    public function getDeal(int $id): array
    {
        return $this->callMethod('crm.deal.get', ['id' => $id]);
    }

    /**
     * Упрощенный метод для получения списка сделок
     * 
     * @param array $params Параметры запроса (может включать filter, order, select и др.)
     * @return array
     *     
     * @throws \RuntimeException В случае ошибки API
     */
    public function getDeal_list(array $params): array
    {
        return $this->callMethod('crm.deal.list', $params);
    }

    /**
     * Получает список сделок с использованием batch-запросов для обхода ограничения на количество возвращаемых записей
     * 
     * Метод сначала выполняет первоначальный запрос для определения общего количества сделок,
     * затем разбивает получение данных на пакетные запросы (batch) по 50 элементов в каждом,
     * автоматически обрабатывая пагинацию и объединяя результаты.
     * 
     * @param array $params Параметры запроса к API Bitrix24, может включать:
     *              - 'filter' => array Фильтры для выборки сделок
     *              - 'order'  => array Параметры сортировки
     *              - 'select' => array Выбираемые поля
     *              - Другие стандартные параметры метода crm.deal.list
     * 
     * @return array Возвращает ассоциативный массив с результатами:
     *              - 'total'  => int Общее количество найденных сделок
     *              - 'req'    => array Массив со всеми полученными сделками
     *              - 'sumReq' => int Количество фактически полученных сделок
     * 
     * @throws \RuntimeException В случае ошибки при выполнении запросов к API
     * 
     * Пример использования:
     * @example
     * $result = $bx24->getDeal_list_batch([
     *     'filter' => ['>OPPORTUNITY' => 5000],
     *     'select' => ['ID', 'TITLE', 'OPPORTUNITY']
     * ]);
     */
    /**
     * Получает список сделок с использованием batch-запросов
     * Оптимизированная версия с исправлением основных проблем
     */
    public function getDealListBatch(array $params, int $batchSize = 50): array
    {
        $firstResult = $this->callMethod('crm.deal.list', $params);
        $total = (int)$firstResult['total'];
        if ($total <= $batchSize) {
            return [
                'total' => $total,
                'req' => $firstResult['result'],
                'sumReq' => count($firstResult['result'])
            ];
        }

        $allDeals = [];
        $batchParams = ['halt' => 0, 'cmd' => []];

        for ($start = 0; $start < $total; $start += $batchSize) {
            $batchParams['cmd'][] = 'crm.deal.list?' . http_build_query(
                array_merge($params, ['start' => $start])
            );

            if (count($batchParams['cmd']) >= 50 || ($start + $batchSize) >= $total) {
                $batchResult = $this->callMethod('batch', $batchParams);

                // Обрабатываем результаты batch-запроса
                foreach ($batchResult['result']['result'] as $batchItems) {
                    array_push($allDeals, ...$batchItems);
                }

                $batchParams['cmd'] = []; // Сбрасываем команды для следующего batch
            }
        }

        return [
            'total' => $total,
            'req' => $allDeals,
            'sumReq' => count($allDeals)
        ];
    }

    /**
     * Создает новую сделку в Bitrix24
     *
     * @param array $params Массив параметров для создания сделки. Должен содержать:
     *              - 'fields' => array Обязательный. Массив полей сделки:
     *                  [
     *                      'TITLE' => string Название сделки (обязательное),
     *                      'STAGE_ID' => string ID стадии сделки,
     *                      'OPPORTUNITY' => float Сумма сделки,
     *                      'CURRENCY_ID' => string Код валюты (например: 'RUB'),
     *                      'CONTACT_ID' => int ID привязанного контакта,
     *                      'COMPANY_ID' => int ID привязанной компании,
     *                      // Другие стандартные поля сделки
     *                      // Пользовательские поля UF_*
     *                  ]
     *              - 'params' => array Необязательный. Дополнительные параметры:
     *                  [
     *                      'REGISTER_SONET_EVENT' => 'Y' // Флаг регистрации события
     *                  ]
     *
     * @return array Возвращает массив с результатом:
     *              [
     *                  'result' => int ID созданной сделки,
     *                  'time' => array Время выполнения запроса
     *              ]
     *
     * @throws \RuntimeException В случае ошибки при создании сделки
     *
     * @example Пример использования:
     * $result = $bx24->addNewDeal([
     *     'fields' => [
     *         'TITLE' => 'Новая сделка',
     *         'STAGE_ID' => 'NEW',
     *         'OPPORTUNITY' => 10000,
     *         'CURRENCY_ID' => 'RUB',
     *         'UF_CRM_CUSTOM_FIELD' => 'Значение'
     *     ],
     *     'params' => [
     *         'REGISTER_SONET_EVENT' => 'Y'
     *     ]
     * ]);
     *
     * @see https://apidocs.bitrix24.ru/api-reference/crm/deals/crm-deal-add.html
     */
    public function addNewDeal(array $params): array
    {
        if (!empty($params)) {
            return $this->callMethod('crm.deal.add', $params);
        } else {
            return ['error' => 'empty params'];
        }
    }


    /**
     * Обновление сделки
     * @param array $params Массив параметров для обновление сделки. Должен содержать:
     *              - 'id' - int id сделки которую хотим обновить
     *              - 'fields' => [] array Обязательный. Массив полей сделки:
     *              - 'params' => [] array Необязательный. Дополнительные параметры:
     * @return array Возвращает массив с результатом
     */
    public function updateDeal(array $params): array
    {
        if (!empty($params)) {
            return $this->callMethod('crm.deal.update', $params);
        } else {
            return ['error' => 'empty params'];
        }
    }

    /**
     * Удаление сделки
     * @param int $id ID сделки
     * @return array Результат удаления сделки
     */
    public function deleteDeal(int $id): array
    {
        if (!empty($id)) {
            return $this->callMethod('crm.deal.delete', ['id' => $id]);
        } else {
            return ['error' => 'empty id'];
        }
    }
    /**
     * Добавить товары (пока не работает)
     * @param int $id ID сделки
     * @return array Результат удаления сделки
     */
    public function addProductRowInDeal(int $id): array
    {
        if (!empty($id)) {
            return $this->callMethod('crm.deal.productrows.set', ['id' => $id]);
        } else {
            return ['error' => 'empty id'];
        }
    }

    /**
     * Получить товары в сделке
     * @param int $id ID сделки
     * @return array Список товаров в сделке
     */
    public function getProductRowInDeal(int $id): array
    {
        if (!empty($id)) {
            return $this->callMethod('crm.deal.productrows.get', ['id' => $id])['result'];
        } else {
            return ['error' => 'empty id'];
        }
    }
}
