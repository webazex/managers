<?php

namespace common\models\Base;

use Yii;
use yii\db\ActiveRecord;
use common\models\core\BaseActiveRecord;

/**
 * Базовый класс для всех пользователей системы
 * (менеджеры, редакторы, администраторы backend)
 */
abstract class BaseAppUser extends BaseActiveRecord
{
    // Константы статусов
    public const STATUS_ACTIVE  = 10;
    public const STATUS_DELETED = 0;
    //public const STATUS_BLOCKED = 5;   // на будущее

    /**
     * Короткое имя роли для быстрого использования в видах и проверках
     * Переопределяется в конкретной модели, если нужно
     */
    abstract public function getRoleName(): string;

    /**
     * Может ли этот пользователь управлять другими пользователями
     * (создавать, редактировать, назначать роли)
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Может ли редактировать данные конкурентов / тарифы
     */
    public function canEditCompetitors(): bool
    {
        return $this->hasRole('editor') || $this->canManageUsers();
    }

    public function canViewAnalytics(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        // permission 'viewAnalytics' можно создать в RBAC, если надо
        // пока просто смотрю, что пользователь авторизован
        return true;

        // или если в будущем добавится конкретный permission то:
        // return Yii::$app->user->can('viewAnalytics');
    }

    /**
     * Доступен ли backend-интерфейс этому пользователю
     * (можно использовать в beforeAction или в layout)
     */
    public function hasBackendAccess(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Удобный хелпер: есть ли у пользователя указанная роль
     * (использует RBAC checkAccess — надёжнее, чем getRoles())
     */
    public function hasRole(string $roleName): bool
    {
        return Yii::$app->authManager->checkAccess($this->getId(), $roleName);
    }

    /**
     * Получить человеко-читаемое название роли (для отображения)
     */
    public function getRoleDisplayName(): string
    {
        $role = $this->getRoleName();

        return match ($role) {
            'admin'     => 'Администратор',
            'editor'    => 'Редактор',
            'manager'   => 'Менеджер',
            default     => 'Пользователь (' . $role . ')',
        };
    }

    // Можно добавить позже:
    // public function getFrontendDashboardUrl(): string { ... }
    // public function getBackendDashboardUrl(): string { ... }
}