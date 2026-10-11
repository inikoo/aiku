<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\OrgAction;
use App\InertiaTable\InertiaTable;
use App\Actions\Procurement\OrgAgent\GetAgentOrderCapacity;
use App\Actions\Procurement\OrgAgent\GetAgentStockCoverBuckets;
use App\Actions\Procurement\OrgAgent\WithAgentShoppingSubNavigation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class IndexAgentCoverBucketItems extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithAgentShoppingSubNavigation;

    private OrgAgent $orgAgent;
    private ?string $bucket;
    private ?string $rank;
    private ?int $supplierId;

    private bool $onDraftsOnly = false;

    public function handle(OrgAgent $orgAgent, ?string $bucket, ?string $rank, ?int $supplierId): LengthAwarePaginator
    {
        $buckets   = GetAgentStockCoverBuckets::make();
        $bucketSql = $buckets->bucketSql($orgAgent);

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->whereRaw('(sp.code collate "C" ilike ? or sp.name ilike ?)', [$like, $like]);
        });
        $supplierFilter = AllowedFilter::callback('supplier', fn ($query, $value) => $query->where('sp.supplier_id', (int) $value));

        $sortBy = fn (string $name, string $expression) => AllowedSort::callback(
            $name,
            fn ($query, bool $descending) => $query->orderByRaw($expression.($descending ? ' desc' : ' asc').' nulls last')
        );

        $queryBuilder = QueryBuilder::for($buckets->scope(OrgSupplierProduct::query()->from('org_supplier_products as osp'), $orgAgent))
            ->join('suppliers as sup', 'sup.id', 'sp.supplier_id')
            ->leftJoin('currencies as cur', 'cur.id', 'sp.currency_id')
            ->when($bucket, fn ($query) => $query->whereRaw("$bucketSql = ?", [$bucket]))
            ->when($rank, fn ($query) => $query->where('os.health_rank', $rank))
            ->when($supplierId, fn ($query) => $query->where('sp.supplier_id', $supplierId))
            ->when($this->onDraftsOnly, fn ($query) => $query->whereRaw($this->onDraftSql()))
            ->select([
                'osp.id',
                'osp.slug',
                'sp.code',
                'sp.name',
                'sp.cost',
                'sp.units_per_carton',
                'sp.minimum_carton_order',
                'sp.measured_lead_time_days',
                'sp.estimated_lead_time_days',
                'sup.code as supplier_code',
                'sup.slug as supplier_slug',
                'cur.code as currency',
                'os.id as org_stock_id',
                'os.quantity_available',
                's.days_of_cover',
                's.recommended_order_quantity',
            ])
            ->selectRaw("$bucketSql as bucket");

        if (!request()->filled('sort')) {
            $queryBuilder
                ->orderByRaw("(os.id is not null and os.state = '".OrgStockStateEnum::ACTIVE->value."') desc")
                ->orderByRaw("case os.health_rank when 'A' then 1 when 'B' then 2 when 'C' then 3 when 'D' then 4 when 'Z' then 5 end nulls last")
                ->when(
                    $bucket === 'dead',
                    fn ($query) => $query->orderByRaw('s.stock_value desc nulls last'),
                    fn ($query) => $query->orderByRaw('s.days_of_cover nulls last')
                )
                ->orderByRaw('sp.code collate "C"');
        }

        return $this->hydrate(
            $queryBuilder
                ->allowedSorts([
                    $sortBy('code', 'sp.code collate "C"'),
                    $sortBy('name', 'sp.name'),
                    $sortBy('supplier_code', 'sup.code collate "C"'),
                    $sortBy('lead_time_days', 'coalesce(sp.measured_lead_time_days, sp.estimated_lead_time_days)'),
                    $sortBy('our_stock', 'os.quantity_available'),
                    $sortBy('our_days_of_cover', 's.days_of_cover'),
                    $sortBy('units_per_carton', 'sp.units_per_carton'),
                    $sortBy('suggested', 's.recommended_order_quantity'),
                ])
                ->allowedFilters([$globalSearch, $supplierFilter])
                ->withPaginator(null, tableName: request()->route()?->getName())
                ->withQueryString()
        );
    }

    private function hydrate(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $rows = collect($paginator->items());

        $webImages = DB::table('product_has_org_stocks')
            ->join('products', 'products.id', 'product_has_org_stocks.product_id')
            ->whereIn('product_has_org_stocks.org_stock_id', $rows->pluck('org_stock_id')->filter())
            ->whereNull('products.deleted_at')
            ->whereNotNull('products.web_images->main')
            ->orderBy('products.id')
            ->pluck('products.web_images', 'product_has_org_stocks.org_stock_id');

        $draftQuantities = $this->draftLines()
            ->whereIn('pot.org_supplier_product_id', $rows->pluck('id'))
            ->groupBy('pot.org_supplier_product_id')
            ->selectRaw('pot.org_supplier_product_id, sum(pot.quantity_ordered) as quantity')
            ->pluck('quantity', 'org_supplier_product_id');

        $paginator->getCollection()->transform(function (OrgSupplierProduct $row) use ($draftQuantities, $webImages) {
            $images = json_decode($webImages->get($row->org_stock_id) ?? 'null', true);

            return [
                'id'                   => $row->id,
                'slug'                 => $row->slug,
                'code'                 => $row->code,
                'name'                 => $row->name,
                'image'                => Arr::get($images, 'main.gallery') ?? Arr::get($images, 'main.thumbnail'),
                'bucket'               => $row->bucket,
                'supplier_code'        => $row->supplier_code,
                'supplier_slug'        => $row->supplier_slug,
                'lead_time_days'       => $row->measured_lead_time_days ?? $row->estimated_lead_time_days,
                'lead_time_measured'   => $row->measured_lead_time_days !== null,
                'our_stock'            => $row->quantity_available !== null ? (float) $row->quantity_available : null,
                'our_days_of_cover'    => $row->days_of_cover !== null ? (int) $row->days_of_cover : null,
                'recommended_quantity' => $row->recommended_order_quantity !== null ? (int) ceil((float) $row->recommended_order_quantity) : null,
                'units_per_carton'     => $row->units_per_carton ? (int) $row->units_per_carton : null,
                'minimum_carton_order' => $row->minimum_carton_order ? (int) $row->minimum_carton_order : null,
                'cost'                 => $row->cost !== null ? (float) $row->cost : null,
                'currency'             => $row->currency,
                'ordered_quantity'     => (float) ($draftQuantities[$row->id] ?? 0),
            ];
        });

        return $paginator;
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('No products found')])
                ->column(key: 'image', label: '', canBeHidden: false)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Product'), canBeHidden: false, sortable: true);

            if ($this->bucket === null) {
                $table->column(key: 'bucket', label: __('Stock group'), canBeHidden: false);
            }

            $table
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'lead_time_days', label: __('Lead time'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'our_stock', label: __('Our stock'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'our_days_of_cover', label: __('Runs out'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'units_per_carton', label: __('Per carton'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'cartons', label: __('Cartons'), canBeHidden: false, align: 'center')
                ->column(key: 'suggested', label: __('Suggested'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'units', label: __('Units'), canBeHidden: false, align: 'right')
                ->column(key: 'amount', label: __('Amount'), canBeHidden: false, align: 'right');
        };
    }

    private function draftLines(): Builder
    {
        return DB::table('purchase_order_transactions as pot')
            ->join('purchase_orders as po', 'po.id', 'pot.purchase_order_id')
            ->where('po.organisation_id', $this->orgAgent->organisation_id)
            ->where('po.agent_id', $this->orgAgent->agent_id)
            ->where('po.parent_type', 'OrgSupplier')
            ->where('po.state', PurchaseOrderStateEnum::IN_PROCESS->value)
            ->whereNull('po.deleted_at')
            ->whereNull('pot.deleted_at');
    }

    /**
     * @param  array{list: array{value: float, lines: int, units: float}, currency: string}  $orderCapacity
     * @return array{count: int, total: float, currency: string, listRoute: array{name: string, parameters: array<int, string>}}
     */
    private function miniCart(array $orderCapacity): array
    {
        return [
            'count'     => $orderCapacity['list']['lines'],
            'total'     => $orderCapacity['list']['value'],
            'currency'  => $orderCapacity['currency'],
            'listRoute' => [
                'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.index',
                'parameters' => [$this->orgAgent->organisation->slug, $this->orgAgent->slug],
            ],
        ];
    }

    private function bucketLabel(): string
    {
        return $this->bucket === null ? __('Browse') : GetAgentStockCoverBuckets::make()->bucketLabel($this->bucket);
    }

    private function onDraftSql(): string
    {
        return "exists (select 1 from purchase_order_transactions pot
            join purchase_orders po on po.id = pot.purchase_order_id and po.deleted_at is null
            where pot.org_supplier_product_id = osp.id and pot.deleted_at is null
                and po.state = '".PurchaseOrderStateEnum::IN_PROCESS->value."')";
    }

    /**
     * What each filter offers, with how many products sit behind every choice. The stock group and
     * rank counts follow the supplier chosen, so they say what picking one would show.
     *
     * @return array{cover: array<int, array{value: string, label: string, count: int}>, rank: array<int, array{value: string, label: string, count: int}>, supplier: array<int, array{value: int, label: string}>}
     */
    private function filterOptions(): array
    {
        $buckets = GetAgentStockCoverBuckets::make();

        $counts = $buckets->scopedQuery($this->orgAgent)
            ->when($this->supplierId, fn ($query) => $query->where('sp.supplier_id', $this->supplierId))
            ->groupByRaw($buckets->bucketSql($this->orgAgent).', os.health_rank')
            ->selectRaw($buckets->bucketSql($this->orgAgent).' as bucket, os.health_rank, count(*) as products')
            ->get();

        $inGroup = $this->bucket === null ? $counts : $counts->where('bucket', $this->bucket);

        return [
            'cover'    => collect(GetAgentStockCoverBuckets::BUCKETS)->map(fn (array $meta, string $bucket) => [
                'value' => $bucket,
                'label' => explode(':', __($meta['label']))[0],
                'count' => (int) $counts->where('bucket', $bucket)->sum('products'),
            ])->values()->all(),
            'rank'     => collect(['A', 'B', 'C', 'D', 'Z'])->map(fn (string $rank) => [
                'value' => $rank,
                'label' => $rank,
                'count' => (int) $inGroup->where('health_rank', $rank)->sum('products'),
            ])->all(),
            'supplier' => $buckets->scopedQuery($this->orgAgent)
                ->join('suppliers as sup', 'sup.id', 'sp.supplier_id')
                ->groupBy('sup.id', 'sup.code', 'sup.name')
                ->orderByRaw('sup.code collate "C"')
                ->selectRaw('sup.id, sup.code, sup.name, count(*) as products')
                ->get()
                ->map(fn ($supplier) => ['value' => (int) $supplier->id, 'label' => $supplier->code.' · '.$supplier->name.' ('.$supplier->products.')'])
                ->all(),
        ];
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): LengthAwarePaginator
    {
        $this->orgAgent   = $orgAgent;
        $cover            = $request->input('cover', 'out');
        $this->bucket     = $request->routeIs('*.shopping.browse.index') ? null : (array_key_exists($cover, GetAgentStockCoverBuckets::BUCKETS) ? $cover : 'out');
        $rank             = $request->input('rank');
        $this->rank       = in_array($rank, ['A', 'B', 'C', 'D', 'Z'], true) ? $rank : null;
        $this->supplierId   = ((int) ($request->input('filter.supplier') ?: $request->input('supplier'))) ?: null;
        $this->onDraftsOnly = $request->boolean('drafts');

        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent, $this->bucket, $this->rank, $this->supplierId);
    }

    public function htmlResponse(LengthAwarePaginator $items, ActionRequest $request): Response
    {
        $orderCapacity = GetAgentOrderCapacity::run($this->orgAgent);

        return Inertia::render(
            'Procurement/AgentCoverBucketItems',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->orgAgent, $request->route()->originalParameters()),
                'title'       => $this->bucketLabel(),
                'pageHead'    => [
                    'icon'          => [
                        'icon'  => ['fal', 'fa-shopping-basket'],
                        'title' => $this->bucketLabel(),
                    ],
                    'model'         => $this->orgAgent->agent->name,
                    'title'         => $this->bucketLabel().($this->rank ? ' · '.$this->rank : ''),
                    'subNavigation' => $this->getAgentShoppingNavigation($this->orgAgent),
                ],
                'orgAgent'    => [
                    'id'       => $this->orgAgent->id,
                    'slug'     => $this->orgAgent->slug,
                    'currency' => $orderCapacity['currency'],
                ],
                'addRoute'    => [
                    'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.line',
                    'parameters' => [$this->orgAgent->organisation->slug, $this->orgAgent->slug],
                ],
                'bucket'      => $this->bucket,
                'bucketLabel' => $this->bucketLabel(),
                'bucketLabels' => collect(GetAgentStockCoverBuckets::BUCKETS)->map(fn (array $meta) => ['label' => __($meta['label']), 'tone' => $meta['tone']])->all(),
                'filterOptions' => $this->filterOptions(),
                'active'      => [
                    'cover'    => $this->bucket,
                    'rank'     => $this->rank,
                    'supplier' => $this->supplierId,
                    'drafts'   => $this->onDraftsOnly,
                    'search'   => (string) $request->input('filter.global', ''),
                ],
                'rank'        => $this->rank,
                'items'       => JsonResource::collection($items),
                'miniCart'    => $this->miniCart($orderCapacity),
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(OrgAgent $orgAgent, array $routeParameters): array
    {
        return array_merge(
            ShowAgentShoppingDashboard::make()->getBreadcrumbs($orgAgent, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $this->bucket === null ? 'grp.org.procurement.org_agents.show.shopping.browse.index' : 'grp.org.procurement.org_agents.show.shopping.items.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => $this->bucketLabel(),
                        'icon'  => 'fal fa-shopping-basket',
                    ],
                ],
            ]
        );
    }
}
