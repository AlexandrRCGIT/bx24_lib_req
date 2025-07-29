<?php

namespace Bx24;

class Bx24Client
{
    private $webhookUrl;
    use Traits\DealTrait;
    use Traits\LeadTrait;
    use Traits\bitrix_processesTrait;
    use Traits\smart_listTrait;
    /**
     * Конструктор класса
     * 
     * @param string $webhookUrl URL вебхука Bitrix24 (например: 'https://portal.bitrix24.ru/rest/int/token/')
     */
    public function __construct(string $webhookUrl)
    {
        $this->webhookUrl = rtrim($webhookUrl, '/') . '/';
    }

    /**
     * Вызов метода Bitrix24 REST API
     * 
     * @param string $method Название метода (например: 'crm.lead.get')
     * @param array $queryData Параметры запроса
     * @return array Результат запроса
     * @throws \RuntimeException В случае ошибки запроса
     */
    public function callMethod(string $method, array $queryData = []): array
    {
        $queryUrl = $this->webhookUrl . $method;
        $queryData = http_build_query($queryData);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ]);

        $result = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \RuntimeException("cURL error: {$error}");
        }

        curl_close($curl);

        $decodedResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        return $decodedResult;
    }
}
