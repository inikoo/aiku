<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentWarehouseStock\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\AgentWarehouseStock;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Goods the agent holds in its own warehouse, received from suppliers and not yet stuffed into a container.
 */
class IndexAgentWarehouseStocks extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function handle(Agent $agent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->where(fn ($query) => $query
                ->whereRaw('supplier_products.code collate "C" ilike ?', [$like])
                ->orWhere('supplier_products.name', 'ilike', $like)
                ->orWhere('suppliers.code', 'ilike', $like));
        });

        $query = QueryBuilder::for(AgentWarehouseStock::class)
            ->where('agent_warehouse_stocks.agent_id', $agent->id)
            ->join('organisations', 'organisations.id', 'agent_warehouse_stocks.organisation_id')
            ->join('supplier_products', 'supplier_products.id', 'agent_warehouse_stocks.supplier_product_id')
            ->leftJoin('suppliers', 'suppliers.id', 'supplier_products.supplier_id')
            ->leftJoin('purchase_orders', 'purchase_orders.id', 'agent_warehouse_stocks.purchase_order_id');

        $query->whereElementGroup(
            key: 'state',
            allowedElements: AgentWarehouseStockStateEnum::values(),
            engine: fn ($query, $elements) => $query->whereIn('agent_warehouse_stocks.state', $elements),
        );

        return $query
            ->select([
                'agent_warehouse_stocks.id',
                'agent_warehouse_stocks.state',
                'agent_warehouse_stocks.quantity',
                'agent_warehouse_stocks.notes',
                'supplier_products.code',
                'supplier_products.name',
                'suppliers.code as supplier_code',
                DB::raw('(select slug from org_supplier_products where supplier_product_id = agent_warehouse_stocks.supplier_product_id and organisation_id = agent_warehouse_stocks.organisation_id order by id limit 1) as org_supplier_product_slug'),
                DB::raw('(select slug from org_suppliers where supplier_id = supplier_products.supplier_id and organisation_id = agent_warehouse_stocks.organisation_id order by id limit 1) as org_supplier_slug'),
                'organisations.code as organisation_code',
                'purchase_orders.reference as purchase_order_reference',
                'purchase_orders.slug as purchase_order_slug',
            ])
            ->defaultSort('code')
            ->allowedSorts(['code', 'name', 'supplier_code', 'organisation_code', 'quantity', 'state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString()
            ->through(fn (AgentWarehouseStock $stock) => [
                'id'                       => $stock->id,
                'state'                    => $stock->state->value,
                'state_icon'               => $stock->state->icon(),
                'quantity'                 => (float) $stock->quantity,
                'notes'                    => $stock->notes,
                'code'                     => $stock->code,
                'name'                     => $stock->name,
                'supplier_code'            => $stock->supplier_code,
                'org_supplier_product_slug' => $stock->org_supplier_product_slug,
                'org_supplier_slug'        => $stock->org_supplier_slug,
                'organisation_code'        => $stock->organisation_code,
                'purchase_order_reference' => $stock->purchase_order_reference,
                'purchase_order_slug'      => $stock->purchase_order_slug,
            ]);
    }

    public function tableStructure(Agent $agent): Closure
    {
        return function (InertiaTable $table) use ($agent) {
            $counts = AgentWarehouseStock::where('agent_id', $agent->id)->selectRaw('state, count(*) as total')->groupBy('state')->pluck('total', 'state');

            $table
                ->elementGroup(
                    key: 'state',
                    label: __('State'),
                    elements: collect(AgentWarehouseStockStateEnum::labels())->map(fn ($label, $state) => [$label, $counts[$state] ?? 0])->all(),
                )
                ->withGlobalSearch()
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('Nothing in the warehouse')])
                ->column(key: 'organisation_code', label: __('For'), canBeHidden: false, sortable: true)
                ->column(key: 'state', label: __('State'), canBeHidden: false, sortable: true)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true)
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'purchase_order_reference', label: __('Purchase order'), canBeHidden: false)
                ->column(key: 'quantity', label: __('Units'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'notes', label: __('Notes'));
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): Agent
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);

        return $agent;
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Procurement/AgentWarehouseStocks',
            [
                'title'       => __('Warehouse'),
                'breadcrumbs' => array_merge(
                    ShowProcurementDashboard::make()->getBreadcrumbs($request->route()->originalParameters()),
                    [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'label' => __('Warehouse'),
                                'route' => [
                                    'name'       => 'grp.org.agent.warehouse.index',
                                    'parameters' => [$this->organisation->slug],
                                ],
                            ],
                        ],
                    ]
                ),
                'pageHead'    => [
                    'icon'  => ['title' => __('Warehouse'), 'icon' => 'fal fa-warehouse-alt'],
                    'title' => __('Warehouse'),
                ],
                'data'        => $this->handle($agent),
            ]
        )->table($this->tableStructure($agent));
    }
}
