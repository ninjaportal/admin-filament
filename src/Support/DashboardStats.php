<?php

namespace NinjaPortal\Admin\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserAppServiceInterface;
use NinjaPortal\Portal\Utils;
use Throwable;

class DashboardStats
{
    /**
     * @return array<string, int|bool>
     */
    public function overview(): array
    {
        $ttl = max((int) config('portal-admin.dashboard.stats.cache_ttl', 300), 0);

        if ($ttl === 0) {
            return $this->resolveOverview();
        }

        return Cache::remember(
            'portal-admin.dashboard.overview-stats',
            now()->addSeconds($ttl),
            fn (): array => $this->resolveOverview(),
        );
    }

    /**
     * @return array<string, int|bool>
     */
    protected function resolveOverview(): array
    {
        [$portalApiProducts, $linkedPortalProducts] = $this->resolvePortalProductCounts();
        [$apigeeTotalProducts, $apigeeUnlinkedProducts, $apigeeProductsAvailable] = $this->resolveApigeeProductCounts();

        return [
            'total_developers' => $this->countDevelopers(),
            'total_apps' => $this->countApps(),
            'portal_api_products' => $portalApiProducts,
            'linked_portal_api_products' => $linkedPortalProducts,
            'apigee_total_products' => $apigeeTotalProducts,
            'apigee_unlinked_products' => $apigeeUnlinkedProducts,
            'apigee_products_available' => $apigeeProductsAvailable,
        ];
    }

    protected function countDevelopers(): int
    {
        $userModel = Utils::getUserModel();

        if (! is_string($userModel) || ! class_exists($userModel)) {
            return 0;
        }

        return (int) $userModel::query()->count();
    }

    protected function countApps(): int
    {
        $userModel = Utils::getUserModel();

        if (! is_string($userModel) || ! class_exists($userModel)) {
            return 0;
        }

        $query = $userModel::query()
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ((bool) config('portal-admin.dashboard.stats.app_count_uses_synced_users_only', true)) {
            $query->where('sync_with_apigee', true);
        }

        $total = 0;

        $query
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$total): void {
                foreach ($users as $user) {
                    try {
                        $total += app(UserAppServiceInterface::class)->all((string) $user->email)->count();
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            });

        return $total;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function resolvePortalProductCounts(): array
    {
        $apiProductModel = Utils::getApiProductModel();

        if (! is_string($apiProductModel) || ! class_exists($apiProductModel)) {
            return [0, 0];
        }

        $query = $apiProductModel::query();

        return [
            (int) $query->count(),
            (int) $apiProductModel::query()
                ->whereNotNull('apigee_product_id')
                ->where('apigee_product_id', '!=', '')
                ->distinct('apigee_product_id')
                ->count('apigee_product_id'),
        ];
    }

    /**
     * @return array{0: int, 1: int, 2: bool}
     */
    protected function resolveApigeeProductCounts(): array
    {
        $apiProductModel = Utils::getApiProductModel();
        $linkedIds = collect();

        if (is_string($apiProductModel) && class_exists($apiProductModel)) {
            /** @var Builder $query */
            $query = $apiProductModel::query();

            $linkedIds = $query
                ->whereNotNull('apigee_product_id')
                ->where('apigee_product_id', '!=', '')
                ->pluck('apigee_product_id')
                ->filter()
                ->unique()
                ->values();
        }

        try {
            $apigeeNames = collect(app(ApiProductServiceInterface::class)->apigeeProducts())
                ->map(fn ($product) => (string) $product->getName())
                ->filter()
                ->unique()
                ->values();

            return [
                $apigeeNames->count(),
                $apigeeNames->diff($linkedIds)->count(),
                true,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [0, 0, false];
        }
    }
}
