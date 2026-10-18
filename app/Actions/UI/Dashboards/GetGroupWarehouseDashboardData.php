<?php

namespace App\Actions\UI\Dashboards;

use App\Actions\Procurement\GetOrganisationStockCoverBuckets;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

class GetGroupWarehouseDashboardData
{
    use AsObject;

    /**
     * Stock health for the Stock tab: active SKOs by days of cover, the same levels as the procurement
     * stock cover (the Aurora quantity status stopped updating in July 2026). Warehouse work and goods
     * in live on the Operations tab.
     *
     * @return array{
     *     totals: array{stock_health: array<string, int>},
     *     stock_levels: array<int, array{bucket: string, label: string, description: string|null, tone: string}>,
     *     organisations: array<int, array{name: string, slug: string, stock_health: array<string, int>, routes: array{stock_health: array{name: string, parameters: array<string, string>}}}>,
     *     routes: array{stock_levels: array{name: string, parameters: array<string, string>}|null}
     * }
     */
    public function handle(Group $group): array
    {
        $buckets       = GetOrganisationStockCoverBuckets::make();
        $shops         = $group->organisations()->where('type', OrganisationTypeEnum::SHOP)->get();
        $stockHealth   = $this->stockHealth($group, $shops->pluck('id')->all());
        $organisations = $shops
            ->map(fn (Organisation $organisation) => [
                'name'         => $organisation->name,
                'slug'         => $organisation->slug,
                'stock_health' => $stockHealth[$organisation->id],
                'routes'       => ['stock_health' => ['name' => 'grp.org.procurement.stock_cover.index', 'parameters' => ['organisation' => $organisation->slug]]],
            ]);

        return [
            'totals'        => [
                'stock_health' => collect(array_keys(GetOrganisationStockCoverBuckets::BUCKETS))
                    ->mapWithKeys(fn (string $bucket) => [$bucket => (int) $organisations->sum("stock_health.$bucket")])
                    ->all(),
            ],
            'stock_levels'  => collect(GetOrganisationStockCoverBuckets::BUCKETS)->map(fn (array $meta, string $bucket) => [
                'bucket'      => $bucket,
                'label'       => $buckets->bucketLabel($bucket),
                'description' => $buckets->bucketDescription($bucket),
                'tone'        => $meta['tone'],
            ])->values()->all(),
            'organisations' => $organisations->values()->all(),
            'routes'        => [
                'stock_levels' => auth()->user()?->hasGroupAccess()
                    ? ['name' => 'grp.dashboard.stock_levels.index', 'parameters' => []]
                    : null,
            ],
        ];
    }

    /**
     * @param array<int, int> $organisationIds
     */
    public static function cacheKey(Group $group, array $organisationIds): string
    {
        sort($organisationIds);

        return "warehouse-dashboard-stock-cover:$group->id:".implode(',', $organisationIds);
    }

    /**
     * One query for every organisation: the bucket is decided row by row, so grouping by organisation
     * gives the same counts as asking each one apart.
     *
     * @param array<int, int> $organisationIds
     *
     * @return array<int, array<string, int>> organisation id => bucket => SKOs
     */
    private function stockHealth(Group $group, array $organisationIds): array
    {
        return Cache::remember(self::cacheKey($group, $organisationIds), now()->addMinutes(10), function () use ($organisationIds) {
            $buckets    = GetOrganisationStockCoverBuckets::make();
            $expression = $buckets->bucketExpression();

            $rows = $buckets->scope(DB::table('org_stocks'), $organisationIds)
                ->selectRaw("org_stocks.organisation_id, $expression as bucket, count(*) as total")
                ->groupBy('org_stocks.organisation_id')
                ->groupByRaw($expression)
                ->get()
                ->groupBy('organisation_id');

            return collect($organisationIds)->mapWithKeys(fn (int $id) => [
                $id => collect(array_keys(GetOrganisationStockCoverBuckets::BUCKETS))
                    ->mapWithKeys(fn (string $bucket) => [$bucket => (int) ($rows->get($id)?->firstWhere('bucket', $bucket)->total ?? 0)])
                    ->all(),
            ])->all();
        });
    }
}
