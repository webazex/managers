<?php

namespace common\interfaces;

interface AuditableInterface
{
    public function getAuditEntityType(): string;
    public function getAuditEntityLabel(): string;
    public function getAuditContext(): array;
}