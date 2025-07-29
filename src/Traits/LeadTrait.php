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

    // Другие методы для работы с лидами
}
