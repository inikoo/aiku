<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-15 15:07
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseStock\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStageEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedSort;

/**
 * The products the agent's suppliers are making: the lines of the purchase orders placed through the agent that are in
 * production, before their quality check passes.
 */
class IndexAgentProductsInProduction extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;
    use WithAgentWarehouseProductRows;

    private function baseQuery(Agent $agent): Builder
    {
        return PurchaseOrderTransaction::query()
            ->join('purchase_orders as production_orders', 'production_orders.id', 'purchase_order_transactions.purchase_order_id')
            ->where('production_orders.agent_id', $agent->id)
            ->whereNull('production_orders.deleted_at')
            ->whereRaw(str_replace('purchase_orders.', 'production_orders.', PurchaseOrderStageEnum::inProductionSql()))
            ->whereIn('purchase_order_transactions.state', [PurchaseOrderTransactionStateEnum::SUBMITTED, PurchaseOrderTransactionStateEnum::CONFIRMED]);
    }

    public function handle(Agent $agent): LengthAwarePaginator
    {
        $query = $this->joinProductRowDetails(
            $this->baseQuery($agent)->select([
                'purchase_order_transactions.id',
                'purchase_order_transactions.quantity_ordered as quantity',
                'production_orders.production_started_at',
                'production_orders.proposed_ready_at',
                'production_orders.approved_ready_at',
            ]),
            'purchase_order_transactions'
        );

        return QueryBuilder::for($query)
            ->defaultSort('code')
            ->allowedSorts([
                AllowedSort::field('code', 'supplier_products.code'),
                AllowedSort::field('name', 'supplier_products.name'),
                AllowedSort::field('supplier_code', 'suppliers.code'),
                AllowedSort::field('organisation_code', 'organisations.code'),
                AllowedSort::field('quantity', 'purchase_order_transactions.quantity_ordered'),
                AllowedSort::field('production_started_at', 'production_orders.production_started_at'),
                AllowedSort::field('ready_at', 'production_orders.approved_ready_at'),
            ])
            ->allowedFilters($this->productRowFilters())
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString()
            ->through(fn (PurchaseOrderTransaction $transaction) => array_merge($this->productRow($transaction), [
                'id'                    => $transaction->id,
                'production_started_at' => $transaction->production_started_at,
                'ready_at'              => $transaction->approved_ready_at ?? $transaction->proposed_ready_at,
                'ready_at_is_approved'  => $transaction->approved_ready_at !== null,
            ]));
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('Nothing is being made by the suppliers')])
                ->column(key: 'organisation_code', label: __('For'), canBeHidden: false, sortable: true)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true)
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'purchase_order_reference', label: __('Purchase order'), canBeHidden: false)
                ->column(key: 'production_started_at', label: __('Production started'), sortable: true)
                ->column(key: 'ready_at', label: __('Ready date'), sortable: true)
                ->column(key: 'quantity', label: __('Units'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'skos', label: __('SKO'), align: 'right')
                ->defaultSort('code');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);
        $this->agent = $agent;

        return $this->handle($agent);
    }

    private Agent $agent;

    public function htmlResponse(LengthAwarePaginator $rows, ActionRequest $request): Response
    {
        $organisationCounts = $this->baseQuery($this->agent)
            ->join('organisations', 'organisations.id', 'purchase_order_transactions.organisation_id')
            ->leftJoin('countries', 'countries.id', 'organisations.country_id')
            ->selectRaw('organisations.slug, organisations.code, organisations.name, countries.name as country_name, count(*) as total')
            ->groupBy('organisations.slug', 'organisations.code', 'organisations.name', 'countries.name')
            ->toBase()
            ->get();

        return $this->productRowsResponse(
            $rows,
            $request,
            __('In production'),
            'fal fa-industry-alt',
            'grp.org.agent.warehouse.in_production',
            'in_production',
            $this->organisationOptions($organisationCounts),
        )->table($this->tableStructure());
    }
}
