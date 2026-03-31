<?php

namespace common\interfaces;

interface AuditableInterface
{
    /**
     * Нужно ли вообще логировать изменения этой модели в данный момент
     */
    public function shouldBeAudited(): bool;

    /**
     * Поля, которые НЕ нужно включать в аудит (timestamps, counters и т.д.)
     */
    public function getAuditIgnoreFields(): array;

    /**
     * человеко-понятное имя сущности (для удобства в логах / UI)
     * Пример: "Тариф провайдера", "Акция"
     */
    public function getAuditEntityLabel(): string;
}