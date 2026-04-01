<?php

namespace backend\models;

use common\models\catalog\Product;
use common\models\catalog\ProductCategory;
use common\models\catalog\Provider;
use common\models\snapshot\MarketSnapshotItem;
use common\models\snapshot\ProviderSnapshotNote;
use common\services\snapshot\SnapshotReaderService;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

final class CompetitorEditorForm extends Model
{
    public ?int $provider_id = null;
    public string $category_code = ProductCategory::CODE_INTERNET;

    /** @var array<string, string|null> */
    public array $prices = [];

    public ?string $promotion_text = null;
    public ?string $loyalty_text = null;
    public ?string $editor_note = null;
    public ?string $comment = null;

    private ?ProductCategory $_category = null;

    public function rules(): array
    {
        return [
            [['provider_id', 'category_code'], 'required'],
            [['provider_id'], 'integer'],
            [['provider_id'], 'exist', 'targetClass' => Provider::class, 'targetAttribute' => 'id'],
            [['category_code'], 'string', 'max' => 64],
            [['category_code'], 'in', 'range' => [
                ProductCategory::CODE_INTERNET,
                ProductCategory::CODE_INTERNET_TV,
            ]],
            [['promotion_text', 'loyalty_text', 'editor_note', 'comment'], 'string'],
            [['promotion_text', 'loyalty_text', 'editor_note', 'comment'], 'filter', 'filter' => 'trim'],
            ['prices', 'validatePrices'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'provider_id' => 'Провайдер',
            'category_code' => 'Категория',
            'promotion_text' => 'Акции',
            'loyalty_text' => 'Лояльность',
            'editor_note' => 'Заметка редактора',
            'comment' => 'Комментарий к сохранению',
        ];
    }

    public function validatePrices(string $attribute): void
    {
        foreach ($this->getProducts() as $product) {
            $raw = $this->prices[$product->code] ?? null;

            if ($raw === null || $raw === '') {
                continue;
            }

            if (!is_numeric($raw)) {
                $this->addError($attribute, "Тариф {$product->name}: цена должна быть числом.");
                continue;
            }

            if ((float)$raw < 0) {
                $this->addError($attribute, "Тариф {$product->name}: цена не может быть отрицательной.");
            }
        }
    }

    public function loadFromLatestSnapshot(SnapshotReaderService $reader): void
    {
        $this->initEmptyPrices();

        if ($this->provider_id === null) {
            return;
        }

        $snapshot = $reader->findLatestSnapshotByCategoryCode($this->category_code);

        if ($snapshot === null) {
            return;
        }

        $items = MarketSnapshotItem::find()
            ->where([
                'snapshot_id' => $snapshot->id,
                'provider_id' => $this->provider_id,
            ])
            ->with(['product'])
            ->all();

        foreach ($items as $item) {
            if ($item->product !== null) {
                $this->prices[$item->product->code] = $item->price !== null ? (string)$item->price : '';
            }
        }

        $note = ProviderSnapshotNote::find()
            ->where([
                'snapshot_id' => $snapshot->id,
                'provider_id' => $this->provider_id,
            ])
            ->one();

        if ($note !== null) {
            $this->promotion_text = $note->promotion_text;
            $this->loyalty_text = $note->loyalty_text;
            $this->editor_note = $note->editor_note;
        }
    }

    /**
     * @return array<string, float|null>
     */
    public function getNormalizedPrices(): array
    {
        $result = [];

        foreach ($this->getProducts() as $product) {
            $raw = $this->prices[$product->code] ?? null;
            $raw = is_string($raw) ? trim($raw) : $raw;

            $result[$product->code] = ($raw === null || $raw === '')
                ? null
                : (float)$raw;
        }

        return $result;
    }

    /**
     * @return array{promotion_text:?string, loyalty_text:?string, editor_note:?string}
     */
    public function getNormalizedNotes(): array
    {
        return [
            'promotion_text' => $this->normalizeNullableString($this->promotion_text),
            'loyalty_text' => $this->normalizeNullableString($this->loyalty_text),
            'editor_note' => $this->normalizeNullableString($this->editor_note),
        ];
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->getCategory()->products;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider_id !== null
            ? Provider::findOne($this->provider_id)
            : null;
    }

    /**
     * @return array<int, string>
     */
    public static function getProviderOptions(): array
    {
        return ArrayHelper::map(
            Provider::find()
                ->where(['is_active' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
                ->all(),
            'id',
            'name'
        );
    }

    private function getCategory(): ProductCategory
    {
        if ($this->_category === null) {
            $this->_category = ProductCategory::find()
                ->where(['code' => $this->category_code])
                ->with(['products'])
                ->one();
        }

        if ($this->_category === null) {
            throw new \RuntimeException('Category not found: ' . $this->category_code);
        }

        return $this->_category;
    }

    private function initEmptyPrices(): void
    {
        foreach ($this->getProducts() as $product) {
            $this->prices[$product->code] ??= '';
        }
    }

    private function normalizeNullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === '' ? null : $value;
    }
}