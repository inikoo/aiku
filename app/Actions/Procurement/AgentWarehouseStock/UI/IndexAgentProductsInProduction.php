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
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilderBase;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedSort;

/**
 * The products the agent's suppliers are making: the lines of the purchase orders placed through the agent that are in
 * production, before their quality check passes, less what already arrived at the agent's warehouse and passed its
 * check. Units the agent rejected are made again, so they stay here even after the order's quality check passed.
 */
class IndexAgentProductsInProduction extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;
    use WithAgentWarehouseProductRows;

    private const string OUTSTANDING_SQL = '(purchase_order_transactions.quantity_ordered - coalesce(agent_receipts.quantity_accepted, 0))';

    private const string REJECTED_SQL = 'coalesce(agent_receipts.quantity_rejected, 0)';

    private function baseQuery(Agent $agent): Builder
    {
        return PurchaseOrderTransaction::query()
            ->join('purchase_orders as production_orders', 'production_orders.id', 'purchase_order_transactions.purchase_order_id')
            ->where('production_orders.agent_id', $agent->id)
            ->whereNull('production_orders.deleted_at')
            ->whereIn('production_orders.state', [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED])
            ->whereIn('purchase_order_transactions.state', [PurchaseOrderTransactionStateEnum::SUBMITTED, PurchaseOrderTransactionStateEnum::CONFIRMED])
            ->leftJoinSub($this->acceptedAtAgentWarehouse(), 'agent_receipts', 'agent_receipts.purchase_order_transaction_id', 'purchase_order_transactions.id')
            ->whereRaw('('.self::inProductionStageSql().' or '.self::REJECTED_SQL.' > 0)')
            ->whereRaw(self::OUTSTANDING_SQL.' > 0');
    }

    private static function inProductionStageSql(): string
    {
        return str_replace('purchase_orders.', 'production_orders.', PurchaseOrderStageEnum::inProductionSql());
    }

    /**
     * Once the order is past production only the rejected units are being made again: the rest is ready or on its way.
     */
    private static function quantityInProductionSql(): string
    {
        return 'case when '.self::inProductionStageSql().' then '.self::OUTSTANDING_SQL.' else least('.self::OUTSTANDING_SQL.', '.self::REJECTED_SQL.') end';
    }

    /**
     * What the agent kept of each line, batch by batch: received less rejected. The rest is still owed by the supplier.
     */
    private function acceptedAtAgentWarehouse(): QueryBuilderBase
    {
        return DB::table('agent_warehouse_receipts')
            ->whereNull('deleted_at')
            ->groupBy('purchase_order_transaction_id')
            ->select('purchase_order_transaction_id')
            ->selectRaw('sum(quantity_received - coalesce(quantity_rejected, 0)) as quantity_accepted')
            ->selectRaw('sum(coalesce(quantity_rejected, 0)) as quantity_rejected');
    }

    public function handle(Agent $agent): LengthAwarePaginator
    {
        $query = $this->joinProductRowDetails(
            $this->baseQuery($agent)->select([
                'purchase_order_transactions.id',
                DB::raw(self::quantityInProductionSql().' as quantity'),
                DB::raw('least('.self::OUTSTANDING_SQL.', '.self::REJECTED_SQL.') as quantity_rejected'),
                DB::raw(self::OUTSTANDING_SQL.' as quantity_outstanding'),
                'production_orders.production_started_at',
                'production_orders.proposed_ready_at',
                'production_orders.approved_ready_at',
            ]),
            'purchase_order_transactions'
        );

        return $this->withProductImages(QueryBuilder::for($query)
            ->defaultSort('code')
            ->allowedSorts([
                AllowedSort::field('code', 'supplier_products.code'),
                AllowedSort::field('name', 'supplier_products.name'),
                AllowedSort::field('supplier_code', 'suppliers.code'),
                AllowedSort::field('organisation_code', 'organisations.code'),
                AllowedSort::field('quantity'),
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
                'quantity_rejected'     => (float) $transaction->quantity_rejected,
                'quantity_outstanding'  => (float) $transaction->quantity_outstanding,
                'receive_route'         => $this->canReceive ? [
                    'name'       => 'grp.models.purchase-order-transaction.agent_warehouse_receipt.store',
                    'parameters' => [$transaction->id],
                ] : null,
            ])));
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Product'), __('Products')])
                ->withEmptyState(['title' => __('Nothing is being made by the suppliers')])
                ->column(key: 'organisation_code', label: __('For'), canBeHidden: false, sortable: true)
                ->column(key: 'image_thumbnail', label: __('Image'), canBeHidden: false)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true)
                ->column(key: 'supplier_code', label: __('Supplier'), canBeHidden: false, sortable: true)
                ->column(key: 'purchase_order_reference', label: __('Purchase order'), canBeHidden: false)
                ->column(key: 'production_started_at', label: __('Production started'), sortable: true)
                ->column(key: 'ready_at', label: __('Ready date'), sortable: true)
                ->column(key: 'quantity', label: __('Units'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'skos', label: __('SKO'), align: 'right');

            if ($this->canReceive) {
                $table->column(key: 'actions', label: '', canBeHidden: false, align: 'right');
            }

            $table->defaultSort('code');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);
        $this->agent      = $agent;
        $this->canReceive = $request->user()->authTo("procurement.$organisation->id.edit");

        return $this->handle($agent);
    }

    private Agent $agent;

    private bool $canReceive = false;

    public function htmlResponse(LengthAwarePaginator $rows, ActionRequest $request): Response
    {
        $organisationCounts = $this->baseQuery($this->agent)
            ->join('organisations', 'organisations.id', 'purchase_order_transactions.organisation_id')
            ->selectRaw('organisations.slug, organisations.code, count(*) as total')
            ->groupBy('organisations.slug', 'organisations.code')
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
        )->with('receiving', $this->canReceive ? [
            'bulk_route' => [
                'name'       => 'grp.models.org.agent_warehouse_receipts.store',
                'parameters' => [$this->organisation->id],
            ],
        ] : null)->table($this->tableStructure());
    }
}
