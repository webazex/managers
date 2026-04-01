<?php
/**
 * @deprecated Legacy active Provider model.
 * Transitional class kept during hard reset refactor.
 * Canonical model is common\models\catalog\Provider.
 */
namespace common\models\Provider;

use common\traits\AuditableTrait;
use yii\db\ActiveRecord;
use yii\db\Expression;
use common\models\Provider\ProviderService;
use common\models\auth\User;
use Yii;

/**
 * Провайдер (конкурент)
 *
 * @property int      $id
 * @property int      $user_id           кто создал / последний редактировал
 * @property string   $title             текущее название провайдера
 * @property int      $type_id           тип провайдера (1 = основной, или что у вас там)
 * @property string   $created_at        datetime
 * @property string   $updated_at        datetime
 * @property int      $provider_name_id  ссылка на запись в таблице названий (история?)
 *
 * Виртуальные / удобные поля для форм и отображения
 * @property-read string $displayTitle
 * @property-read ProviderName|null $currentNameRecord
 */
class Provider extends ActiveRecord
{
    use AuditableTrait;

    public bool $auditEnabled = true;
    public string $auditSnapshotType = 'snapshot';

    // Есил в условной админке манагер переименовывает провайдера
    public ?string $name = null;     // ← маппится на title
    public ?array $service_ids = [];

    public static function tableName(): string
    {
        return '{{%provider}}';
    }

    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],

            [['user_id'], 'integer'],
            [['user_id'], 'exist',
                'targetClass'     => User::class,
                'targetAttribute' => 'id',
                'skipOnEmpty'     => true,
            ],

            [['type_id'], 'integer'],
            [['type_id'], 'exist',
                'targetClass'     => ProviderType::class,
                'targetAttribute' => 'id',
                'skipOnEmpty'     => true,
            ],

            [['provider_name_id'], 'integer'],
            [['provider_name_id'], 'exist',
                'targetClass'     => ProviderName::class,
                'targetAttribute' => 'id',
            ],
            [['service_ids'], 'safe'],   // ← массив id услуг

        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'                => 'ID',
            'user_id'           => 'Создал / редактор',
            'title'             => 'Название',
            'name'              => 'Название',           // для формы
            'type_id'           => 'Тип провайдера',
            'provider_name_id'  => 'ID записи названия',
            'created_at'        => 'Создан',
            'updated_at'        => 'Обновлён',
        ];
    }

    /**
     * Синхронизация виртуального поля name ↔ title
     */
    public function afterFind(): void
    {
        parent::afterFind();
        $this->name = $this->title;
        $this->service_ids = $this->getServiceIds();
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Синхронизация title ↔ name (для удобства в формах)
        if ($this->name !== null) {
            $this->title = trim($this->name);
        }

        if (empty($this->title)) {
            $this->addError('title', 'Название провайдера обязательно');
            return false;
        }

        // Автоматическое заполнение user_id
        $currentUserId = Yii::$app->user?->getId();
        if ($insert && $this->user_id === null && $currentUserId !== null) {
            $this->user_id = $currentUserId;
        }

        // ─── Работа со справочником названий ───────────────────────────────
        $titleChanged = $this->isAttributeChanged('title') || $insert;

        if ($titleChanged) {
            // Ищем существующую запись по точному названию
            $existingName = ProviderName::findOne(['name' => $this->title]);

            if ($existingName) {
                // Уже есть → просто ссылаемся
                $this->provider_name_id = $existingName->id;
            } else {
                // Создаём новую нормализованную запись
                $nameRecord = new ProviderName();
                $nameRecord->name = $this->title;

                if (!$nameRecord->save()) {
                    $this->addError('title', 'Не удалось добавить название в справочник');
                    $this->addErrors($nameRecord->errors);
                    return false;
                }

                $this->provider_name_id = $nameRecord->id;
            }
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->service_ids !== null) {
            // Очищаем старые связи
            $this->unlinkAll('services', true);

            // Добавляем новые
            $ids = array_filter(array_map('intval', $this->service_ids));
            if ($ids) {
                $services = ProviderService::findAll(['id' => $ids]);
                foreach ($services as $service) {
                    $this->link('services', $service);
                }
            }
        }
    }

    // ─── Связи ─────────────────────────────────────────────────────────────

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getServices()
    {
        return $this->hasMany(ProviderService::class, ['id' => 'service_id'])
            ->viaTable('{{%provider_service_link}}', ['provider_id' => 'id'])
            ->orderBy('sort_order ASC');
    }

    public function getServiceIds(): array
    {
        return $this->getServices()->select('id')->column();
    }

    public function getServiceCodes(): array
    {
        return $this->getServices()->select('code')->column();
    }

    public function hasService(string $code): bool
    {
        return in_array($code, $this->getServiceCodes(), true);
    }

    public function isInternetProvider(): bool { return $this->hasService('internet'); }
    public function isTvProvider():       bool { return $this->hasService('tv');       }
    public function isDomainsProvider():  bool { return $this->hasService('domains');  }

    public function getServicesAsString(string $glue = ', '): string
    {
        return implode($glue, $this->getServices()->select('name')->column());
    }

    public function getType()
    {
        return $this->hasOne(ProviderType::class, ['id' => 'type_id']);
    }

    public function getCurrentNameRecord()
    {
        return $this->hasOne(ProviderName::class, ['id' => 'provider_name_id']);
    }

    // ─── Удобные геттеры для вида / грида / API ───────────────────────────

    public function getDisplayTitle(): string
    {
        return $this->title ?: '(без названия)';
    }

    public function getDisplayTypeName(): ?string
    {
        return $this->type?->name ?? null;
    }

    public function getCreatedByName(): ?string
    {
        return $this->user?->username ?? $this->user?->login ?? null;
    }

    public function getNameRecord()
    {
        return $this->hasOne(ProviderName::class, ['id' => 'provider_name_id']);
    }

    public function getDisplayName(): string
    {
        // Приоритет: нормализованное название → title → fallback
        return $this->nameRecord?->name ?? $this->title ?? '(без названия)';
    }

    public function getDisplayNameForCompare(): string
    {
        // Нормал
        return $this->nameRecord?->name ?? $this->title;
    }
    public function getHasInternet(): bool { return $this->hasService('internet'); }
    public function getHasTv(): bool       { return $this->hasService('tv');       }
    public function getHasDomains(): bool  { return $this->hasService('domains');  }
    public function getServicesNames(string $glue = ', '): string
    {
        return implode($glue, $this->getServices()->select('name')->column());
    }
}