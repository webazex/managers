<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\models\catalog\Provider;
use common\services\snapshot\SnapshotReaderService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

final class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect('/dashboard');
        }

        $this->layout = 'blank';

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('/dashboard');
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionDashboard(string $tab = 'overview'): string
    {
        $allowedTabs = ['overview', 'comparison', 'internet', 'bundle', 'promotions', 'adddata'];

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        if ($tab === 'adddata' && !Yii::$app->user->can('editCompetitors')) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        $internetSection = $this->buildCategorySectionData('internet');
        $bundleSection = $this->buildCategorySectionData('internet_tv');
        $promotionsSection = $this->buildPromotionsSectionData();
        $comparisonSection = $this->buildComparisonSectionData();

        return $this->render('dashboard', [
            'activeTab' => $tab,
            'currentUserName' => $this->resolveCurrentUserName(),
            'currentUserRole' => $this->resolveCurrentUserRoleLabel(),
            'availableTabs' => $this->resolveAvailableTabs(),
            'internetSection' => $internetSection,
            'bundleSection' => $bundleSection,
            'promotionsSection' => $promotionsSection,
            'comparisonSection' => $comparisonSection,
        ]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->redirect('/login');
    }

    private function resolveCurrentUserName(): string
    {
        $identity = Yii::$app->user->identity;

        if ($identity === null) {
            return 'Гость';
        }

        return $identity->username
            ?? $identity->email
            ?? ('Пользователь #' . Yii::$app->user->id);
    }

    private function resolveCurrentUserRoleLabel(): string
    {
        $roles = Yii::$app->authManager->getRolesByUser((int) Yii::$app->user->id);
        $roleNames = array_keys($roles);

        if (in_array('admin', $roleNames, true)) {
            return 'Администратор';
        }

        if (in_array('editor', $roleNames, true)) {
            return 'Редактор';
        }

        if (in_array('manager', $roleNames, true)) {
            return 'Менеджер';
        }

        return 'Без роли';
    }

    private function resolveAvailableTabs(): array
    {
        $tabs = [
            'overview' => 'Профиль',
        ];

        if (Yii::$app->user->can('viewAnalytics')) {
            $tabs['comparison'] = 'Сравнение';
            $tabs['internet'] = 'Интернет';
            $tabs['bundle'] = 'Интернет + ТВ';
            $tabs['promotions'] = 'Акции';
        }

        if (Yii::$app->user->can('editCompetitors')) {
            $tabs['adddata'] = 'Редактор';
        }

        return $tabs;
    }

    private function buildCategorySectionData(string $categoryCode): array
    {
        /** @var SnapshotReaderService $reader */
        $reader = Yii::createObject(SnapshotReaderService::class);

        $snapshot = $reader->findLatestSnapshotByCategoryCode($categoryCode);

        if ($snapshot === null) {
            return [
                'snapshot' => null,
                'providers' => [],
                'products' => [],
                'matrix' => [],
                'providerCount' => 0,
                'avgPrice' => 0,
                'minPrice' => 0,
                'maxPrice' => 0,
                'changedCells' => 0,
                'chartProducts' => [],
                'chartProviders' => [],
            ];
        }

        $items = $reader->getSnapshotItems((int) $snapshot->id);

        $providers = [];
        $products = [];
        $matrix = [];
        $prices = [];
        $changedCells = 0;

        foreach ($items as $item) {
            $providerCode = $item->provider?->code ?? ('provider_' . $item->provider_id);
            $providerName = $item->provider?->name ?? $providerCode;
            $productCode = $item->product?->code ?? ('product_' . $item->product_id);
            $productName = $item->product?->name ?? $productCode;

            $providers[$providerCode] = $providerName;
            $products[$productCode] = [
                'code' => $productCode,
                'name' => $productName,
            ];

            $matrix[$providerCode][$productCode] = [
                'price' => $item->price !== null ? (float) $item->price : null,
                'source_type' => $item->source_type,
                'is_copied' => (bool) $item->is_copied,
                'changed_in_snapshot' => (bool) $item->changed_in_snapshot,
            ];

            if ($item->price !== null) {
                $prices[] = (float) $item->price;
            }

            if ((bool) $item->changed_in_snapshot) {
                $changedCells++;
            }
        }

        uasort($products, static function (array $a, array $b): int {
            $aNum = (int) preg_replace('/\D+/', '', $a['code']);
            $bNum = (int) preg_replace('/\D+/', '', $b['code']);

            if ($aNum === $bNum) {
                return strcmp($a['code'], $b['code']);
            }

            return $aNum <=> $bNum;
        });

        asort($providers);

        $chartProducts = array_values($products);
        $chartProviders = [];

        foreach ($providers as $providerCode => $providerName) {
            $providerPrices = [];

            foreach ($products as $productCode => $productData) {
                $providerPrices[$productCode] = $matrix[$providerCode][$productCode]['price'] ?? null;
            }

            $chartProviders[] = [
                'code' => $providerCode,
                'name' => $providerName,
                'prices' => $providerPrices,
            ];
        }

        return [
            'snapshot' => $snapshot,
            'providers' => $providers,
            'products' => $products,
            'matrix' => $matrix,
            'providerCount' => count($providers),
            'avgPrice' => $prices !== [] ? round(array_sum($prices) / count($prices)) : 0,
            'minPrice' => $prices !== [] ? min($prices) : 0,
            'maxPrice' => $prices !== [] ? max($prices) : 0,
            'changedCells' => $changedCells,
            'chartProducts' => $chartProducts,
            'chartProviders' => $chartProviders,
        ];
    }

    private function buildPromotionsSectionData(): array
    {
        /** @var SnapshotReaderService $reader */
        $reader = Yii::createObject(SnapshotReaderService::class);
        $snapshot = $reader->findLatestSnapshotByCategoryCode('internet');

        if ($snapshot === null) {
            return [
                'snapshot' => null,
                'notes' => [],
            ];
        }

        $notes = [];
        foreach ($snapshot->providerNotes as $note) {
            $providerCode = $note->provider?->code ?? ('provider_' . $note->provider_id);

            $notes[$providerCode] = [
                'provider_name' => $note->provider?->name ?? $providerCode,
                'promotion_text' => $note->promotion_text,
                'loyalty_text' => $note->loyalty_text,
                'editor_note' => $note->editor_note,
                'source_type' => $note->source_type,
                'changed_in_snapshot' => (bool) $note->changed_in_snapshot,
            ];
        }

        uasort($notes, static fn(array $a, array $b): int => strcmp($a['provider_name'], $b['provider_name']));

        return [
            'snapshot' => $snapshot,
            'notes' => $notes,
        ];
    }

    private function buildComparisonSectionData(): array
    {
        /** @var SnapshotReaderService $reader */
        $reader = Yii::createObject(SnapshotReaderService::class);
        $snapshot = $reader->findLatestSnapshotByCategoryCode('internet');

        $providers = Provider::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        $providerOptions = [];
        foreach ($providers as $provider) {
            $providerOptions[] = [
                'id' => (int) $provider->id,
                'code' => $provider->code,
                'name' => $provider->name,
            ];
        }

        if ($snapshot === null || count($providerOptions) < 2) {
            return [
                'snapshot' => $snapshot,
                'providers' => $providerOptions,
                'providerAId' => null,
                'providerBId' => null,
                'rows' => [],
            ];
        }

        $providerAId = $providerOptions[0]['id'];
        $providerBId = $providerOptions[1]['id'];

        return [
            'snapshot' => $snapshot,
            'providers' => $providerOptions,
            'providerAId' => $providerAId,
            'providerBId' => $providerBId,
            'rows' => $reader->buildComparisonRows((int) $snapshot->id, $providerAId, $providerBId),
        ];
    }
}