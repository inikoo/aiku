<?php

namespace App\Actions\Procurement\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\GetOrganisationStockCoverBuckets;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Http\Resources\Procurement\StockCoverOrgStocksResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStock;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Every shop organisation's SKOs at one stock level, the list behind the group dashboard's stock
 * health bar. Levels are decided per SKO exactly as each organisation's own stock cover page does,
 * so a level here holds the sum of that level across the organisations. Stock value stays in each
 * organisation's own currency and is never added up across them.
 */
class IndexGroupStockCoverItems extends OrgAction
{
    private ?array $bucketElements = null;

    /** @var array<int, int>|null */
    private ?array $organisationIds = null;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasGroupAccess();
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->group);
    }

    /**
     * @return array<int, int>
     */
    private function organisationIds(Group $group): array
    {
        return $this->organisationIds ??= $group->organisations()
            ->where('type', OrganisationTypeEnum::SHOP)
            ->pluck('id')
            ->all();
    }

    protected function getElementGroups(Group $group): array
    {
        $buckets = GetOrganisationStockCoverBuckets::make();

        return [
            'cover' => [
                'label'    => __('Stock level'),
                'default'  => 'out',
                'elements' => $this->bucketElements ??= $this->bucketElements($group),
                'engine'   => fn (QueryBuilder $query, array $elements) => $buckets->whereBuckets($query->getEloquentBuilder(), $elements),
            ],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    private function bucketElements(Group $group): array
    {
        $buckets    = GetOrganisationStockCoverBuckets::make();
        $expression = $buckets->bucketExpression();

        $counts = $buckets->scope(DB::table('org_stocks'), $this->organisationIds($group))
            ->selectRaw("$expression as bucket, count(*) as total")
            ->groupByRaw($expression)
            ->pluck('total', 'bucket');

        return collect(GetOrganisationStockCoverBuckets::BUCKETS)
            ->mapWithKeys(fn (array $meta, string $bucket) => [$bucket => [$buckets->bucketLabel($bucket), (int) ($counts[$bucket] ?? 0)]])
            ->all();
    }

    public function handle(Group $group, ?string $prefix = null): LengthAwarePaginator
    {
        $buckets = GetOrganisationStockCoverBuckets::make();

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stocks.code', $value)
                    ->orWhereAnyWordStartWith('org_stocks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgStock::class);
        $buckets->scope($queryBuilder->getEloquentBuilder(), $this->organisationIds($group));

        foreach ($this->getElementGroups($group) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix,
                default: $elementGroup['default'],
                optional: true,
            );
        }

        $firstWarehouse = DB::table('warehouses')
            ->whereColumn('warehouses.organisation_id', 'org_stocks.organisation_id')
            ->orderBy('warehouses.id')
            ->select('warehouses.slug')
            ->limit(1);

        return $queryBuilder
            ->defaultSort('days_of_cover')
            ->leftJoin('org_stock_families', 'org_stock_families.id', 'org_stocks.org_stock_family_id')
            ->join('organisations', 'organisations.id', 'org_stocks.organisation_id')
            ->leftJoin('currencies', 'currencies.id', 'organisations.currency_id')
            ->select([
                'org_stocks.id',
                'org_stocks.slug',
                'org_stocks.code',
                'org_stocks.name',
                'org_stock_families.code as family_code',
                'org_stocks.health_rank',
                'org_stocks.quantity_available',
                'org_stock_stats.days_of_cover',
                'org_stock_stats.stock_value',
                'org_stock_stats.on_the_way_po_count',
                'org_stock_stats.recommended_order_quantity',
                'org_stock_stats.projected_lost_revenue',
                'sp.supplier_code',
                'organisations.slug as organisation_slug',
                'organisations.code as organisation_code',
                'organisations.name as organisation_name',
                'currencies.code as currency_code',
            ])
            ->selectSub($firstWarehouse, 'warehouse_slug')
            ->selectRaw($buckets->leadTimeExpression().' as lead_time_days')
            ->selectRaw($buckets->bucketExpression().' as bucket')
            ->allowedSorts(['code', 'name', 'family_code', 'organisation_code', 'health_rank', 'quantity_available', 'days_of_cover', 'lead_time_days', 'supplier_code', 'stock_value', 'projected_lost_revenue'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group $group, ?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($group, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($group) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements'],
                    default: $elementGroup['default'],
                );
            }

            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('SKO'), __('SKOs')])
                ->defaultSort('days_of_cover')
                ->column(key: 'organisation_code', label: __('Organisation'), sortable: true)
                ->column(key: 'code', label: __('Reference'), sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), sortable: true, searchable: true)
                ->column(key: 'family_code', label: __('Family'), sortable: true)
                ->column(key: 'health_rank', label: __('Rank'), sortable: true)
                ->column(key: 'bucket_label', label: __('Stock level'))
                ->column(key: 'quantity_available', label: __('Stock'), sortable: true, align: 'right')
                ->column(key: 'days_of_cover', label: __('Days of cover'), sortable: true, align: 'right')
                ->column(key: 'lead_time_days', label: __('Lead time'), sortable: true, align: 'right')
                ->column(key: 'supplier_code', label: __('Supplier'), sortable: true)
                ->column(key: 'recommended_quantity', label: __('Suggested order'), align: 'right')
                ->column(key: 'projected_lost_revenue', label: __('Lost if not ordered'), tooltip: __('Sales lost over the lead time and a month after, if nothing more is ordered'), sortable: true, align: 'right')
                ->column(key: 'stock_value', label: __('Stock value'), sortable: true, align: 'right');
        };
    }

    public function htmlResponse(LengthAwarePaginator $orgStocks, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrganisationStockCoverItems',
            [
                'breadcrumbs'   => $this->getBreadcrumbs(),
                'title'         => __('Stock levels'),
                'pageHead'      => [
                    'icon'       => [
                        'icon'  => ['fal', 'fa-box'],
                        'title' => __('Stock levels'),
                    ],
                    'title'      => __('Stock levels'),
                    'afterTitle' => [
                        'label' => __('In group'),
                    ],
                ],
                'currency'      => null,
                'warehouseSlug' => null,
                'exportRoute'   => null,
                'data'          => StockCoverOrgStocksResource::collection($orgStocks),
            ]
        )->table($this->tableStructure($this->group));
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowGroupDashboard::make()->getBreadcrumbs(__('Dashboard')),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.dashboard.stock_levels.index',
                        ],
                        'label' => __('Stock levels'),
                    ],
                ],
            ]
        );
    }
}
