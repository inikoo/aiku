<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\WithAgentShoppingSubNavigation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Enums\Procurement\OrgAgent\OrgAgentOrderContainerEnum;
use App\InertiaTable\InertiaTable;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\Procurement\OrgAgent;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * The agent keeps what its suppliers deliver in its own warehouse until there is enough to book a container.
 * This is that stock for one organisation: the stock delivery the agent is filling, before it is invoiced and sent.
 */
class ShowAgentContainerInPreparation extends OrgAction
{
    use WithAgentShoppingSubNavigation;
    use WithProcurementAuthorisation;

    private const string CARTONS_SQL = 'sdi.unit_quantity / nullif(sp.units_per_carton, 0)';

    private const string CBM_SQL = 'coalesce(sdi.cbm_in_container, '.self::CARTONS_SQL.' * sp.cbm)';

    private OrgAgent $orgAgent;

    private StockDelivery $stockDelivery;

    private function lines(StockDelivery $stockDelivery): Builder
    {
        return StockDeliveryItem::query()->withoutGlobalScopes()->from('stock_delivery_items as sdi')
            ->leftJoin('supplier_products as sp', 'sp.id', 'sdi.supplier_product_id')
            ->leftJoin('suppliers as sup', 'sup.id', 'sp.supplier_id')
            ->where('sdi.stock_delivery_id', $stockDelivery->id)
            ->whereNull('sdi.deleted_at')
            ->where('sdi.state', '!=', StockDeliveryItemStateEnum::CANCELLED->value);
    }

    public function handle(StockDelivery $stockDelivery): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->whereRaw('(sp.code ilike ? or sp.name ilike ? or sup.code ilike ?)', [$like, $like, $like]);
        });

        $sortBy = fn (string $name, string $expression) => AllowedSort::callback(
            $name,
            fn ($query, bool $descending) => $query->orderByRaw($expression.($descending ? ' desc' : ' asc').' nulls last')
        );

        $query = $this->lines($stockDelivery)
            ->selectRaw('sdi.id, sp.code, sp.name, sup.code as supplier_code, sup.name as supplier_name, sdi.net_amount as amount,
                '.self::CARTONS_SQL.' as cartons, '.self::CBM_SQL.' as cbm');

        if (!request()->filled('sort')) {
            $query->orderByRaw('sup.code collate "C"')->orderByRaw('sp.code collate "C"');
        }

        $rows = QueryBuilder::for($query)
            ->allowedSorts([
                $sortBy('code', 'sp.code collate "C"'),
                $sortBy('name', 'sp.name'),
                $sortBy('supplier_code', 'sup.code collate "C"'),
                $sortBy('cartons', self::CARTONS_SQL),
                $sortBy('amount', 'sdi.net_amount'),
                $sortBy('cbm', self::CBM_SQL),
            ])
            ->allowedFilters([$globalSearch])
            ->orderBy('sdi.id')
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString();

        $rows->getCollection()->transform(fn ($row) => [
            'id'            => $row->id,
            'code'          => $row->code,
            'name'          => $row->name,
            'supplier_code' => $row->supplier_code,
            'supplier_name' => $row->supplier_name,
            'cartons'       => $row->cartons !== null ? round((float) $row->cartons, 2) : null,
            'amount'        => (float) $row->amount,
            'cbm'           => $row->cbm !== null ? round((float) $row->cbm, 3) : null,
        ]);

        return $rows;
    }

    /**
     * @return array{products: int, suppliers: int, cartons: float, amount: float, cbm: float, products_without_volume: int, purchase_orders: int, days: int}
     */
    public function summary(StockDelivery $stockDelivery): array
    {
        $totals = $this->lines($stockDelivery)
            ->selectRaw('count(*) as products, count(distinct sp.supplier_id) as suppliers,
                coalesce(sum('.self::CARTONS_SQL.'), 0) as cartons,
                coalesce(sum(sdi.net_amount), 0) as amount,
                coalesce(sum('.self::CBM_SQL.'), 0) as cbm,
                count(*) filter (where sdi.cbm_in_container is null and (sp.cbm is null or coalesce(sp.units_per_carton, 0) = 0)) as products_without_volume')
            ->toBase()
            ->first();

        return [
            'products'                => (int) $totals->products,
            'suppliers'               => (int) $totals->suppliers,
            'cartons'                 => round((float) $totals->cartons, 2),
            'amount'                  => (float) $totals->amount,
            'cbm'                     => round((float) ($stockDelivery->cbm ?? $totals->cbm), 2),
            'products_without_volume' => (int) $totals->products_without_volume,
            'purchase_orders'         => (int) $stockDelivery->number_purchase_orders,
            'days'                    => (int) Carbon::parse($stockDelivery->date ?? $stockDelivery->created_at)->diffInDays(now()),
        ];
    }

    public static function fillPercent(float $cbm, ?float $containerCbm): float
    {
        return $containerCbm ? round($cbm / $containerCbm * 100, 1) : 0.0;
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('Nothing in the warehouse of the agent yet')])
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Product'), canBeHidden: false, sortable: true)
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'cartons', label: __('Cartons'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'amount', label: __('Total'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'cbm', label: __('Volume'), canBeHidden: false, sortable: true, align: 'right');
        };
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, StockDelivery $stockDelivery, ActionRequest $request): LengthAwarePaginator
    {
        $this->orgAgent      = $orgAgent;
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($organisation, $request);

        abort_unless(
            ShowAgentOrderStage::documents($orgAgent, ShowAgentOrderStage::PREPARING)
                ->where('sd.id', $stockDelivery->id)
                ->exists(),
            404
        );

        return $this->handle($stockDelivery);
    }

    public function htmlResponse(LengthAwarePaginator $rows, ActionRequest $request): Response
    {
        $title       = __('In the warehouse of the agent');
        $currency    = $this->stockDelivery->currency;
        $orgCurrency = $this->organisation->currency;
        $summary     = $this->summary($this->stockDelivery);
        $fill        = self::fillPercent($summary['cbm'], $this->orgAgent->order_container->cbm());

        return Inertia::render(
            'Procurement/AgentContainerInPreparation',
            [
                'breadcrumbs'   => $this->getBreadcrumbs($this->orgAgent, $this->stockDelivery, $request->route()->originalParameters()),
                'title'         => $title,
                'pageHead'      => [
                    'icon'          => ['icon' => ['fal', 'fa-box-open'], 'title' => $title],
                    'model'         => $this->orgAgent->agent->name,
                    'title'         => $title,
                    'subNavigation' => $this->getAgentShoppingNavigation($this->orgAgent),
                ],
                'agentName'     => $this->orgAgent->agent->name,
                'stockDelivery' => [
                    'reference'   => $this->stockDelivery->reference,
                    'state_label' => $this->stockDelivery->state->labels()[$this->stockDelivery->state->value],
                    'url'         => route('grp.org.procurement.stock_deliveries.show', [$this->organisation->slug, $this->stockDelivery->slug]),
                ],
                'listUrl'       => route('grp.org.procurement.org_agents.show.shopping.stage', [$this->organisation->slug, $this->orgAgent->slug, ShowAgentOrderStage::PREPARING, 'scope' => 'all']),
                'currency'      => $currency?->code,
                'orgCurrency'   => $orgCurrency->code,
                'orgExchange'   => $currency && $currency->id !== $orgCurrency->id ? GetCurrencyExchange::run($currency, $orgCurrency) : null,
                'container'     => [
                    'label' => OrgAgentOrderContainerEnum::labels()[$this->orgAgent->order_container->value],
                    'cbm'              => $this->orgAgent->order_container->cbm(),
                    'fill'             => $fill,
                    'is_worth_booking' => $fill >= 90,
                ],
                'summary'       => $summary,
                'rows'          => JsonResource::collection($rows),
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(OrgAgent $orgAgent, StockDelivery $stockDelivery, array $routeParameters): array
    {
        return array_merge(
            ShowAgentShoppingDashboard::make()->getBreadcrumbs($orgAgent, array_slice($routeParameters, 0, 2)),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.procurement.org_agents.show.shopping.container',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Preparing'),
                        'icon'  => 'fal fa-box-open',
                    ],
                ],
            ]
        );
    }
}
