<?php

namespace common\traits;

trait ProvidesAuditContextTrait
{
    public function getAuditEntityType(): string
    {
        return static::class;
    }

    public function getAuditEntityLabel(): string
    {
        return method_exists($this, '__toString')
            ? (string) $this
            : static::class . '#' . ($this->id ?? 'new');
    }

    public function getAuditContext(): array
    {
        return [];
    }
}