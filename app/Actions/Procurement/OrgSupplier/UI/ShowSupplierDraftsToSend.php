<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgSupplier\WithOrgSupplierSubNavigation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class ShowSupplierDraftsToSend extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithOrgSupplierSubNavigation;

    private OrgSupplier $orgSupplier;

    /**
     * Our draft purchase orders to an independent supplier that hold at least one product. Each
     * organisation orders on its own, so unlike an agent nobody else's drafts are shown.
     */
    public static function drafts(OrgSupplier $orgSupplier): Builder
    {
        return DB::table('purchase_orders as po')
            ->where('po.organisation_id', $orgSupplier->organisation_id)
            ->where('po.parent_type', 'OrgSupplier')
            ->where('po.parent_id', $orgSupplier->id)
            ->where('po.state', PurchaseOrderStateEnum::IN_PROCESS->value)
            ->whereNull('po.deleted_at')
            ->whereExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('purchase_order_transactions as line')
                ->whereColumn('line.purchase_order_id', 'po.id')
                ->whereNull('line.deleted_at'));
    }

    /**
     * The products on our drafts to this supplier, one row each.
     */
    public function handle(OrgSupplier $orgSupplier): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('sp.code', $value)
                    ->orWhereStartWith('sp.name', $value);
            });
        });

        return QueryBuilder::for(PurchaseOrderTransaction::query())
            ->join('supplier_products as sp', 'sp.id', 'purchase_order_transactions.supplier_product_id')
            ->whereIn('purchase_order_transactions.purchase_order_id', self::drafts($orgSupplier)->select('po.id'))
            ->whereNull('purchase_order_transactions.deleted_at')
            ->selectRaw('purchase_order_transactions.id, sp.code, sp.name, purchase_order_transactions.quantity_ordered as units,
                purchase_order_transactions.quantity_ordered / nullif(sp.units_per_carton, 0) as cartons,
                purchase_order_transactions.unit_cost, purchase_order_transactions.net_amount as amount,
                purchase_order_transactions.quantity_ordered / nullif(sp.units_per_carton, 0) * sp.cbm as cbm')
            ->defaultSort('code')
            ->allowedFilters([$globalSearch])
            ->allowedSorts(['code', 'name', 'units', 'cartons', 'unit_cost', 'amount', 'cbm'])
            ->withPaginator(null, tableName: request()->route()->getName())
            ->withQueryString();
    }

    /**
     * @return array{purchase_order_id: int, reference: string, url: string, lines: int, amount: float, cbm: float}|null
     */
    private function draftSummary(OrgSupplier $orgSupplier): ?array
    {
        $draft = self::drafts($orgSupplier)
            ->join('purchase_order_transactions as pot', fn ($join) => $join->on('pot.purchase_order_id', 'po.id')->whereNull('pot.deleted_at'))
            ->join('supplier_products as sp', 'sp.id', 'pot.supplier_product_id')
            ->groupBy('po.id')
            ->selectRaw('po.id, po.slug, po.reference, count(pot.id) as lines, coalesce(sum(pot.net_amount), 0) as amount,
                coalesce(sum(pot.quantity_ordered / nullif(sp.units_per_carton, 0) * sp.cbm), 0) as cbm')
            ->first();

        return $draft ? [
            'purchase_order_id' => $draft->id,
            'reference'         => $draft->reference,
            'url'               => route('grp.org.procurement.org_suppliers.show.purchase-orders.show', [$this->organisation->slug, $orgSupplier->slug, $draft->slug]),
            'lines'             => (int) $draft->lines,
            'amount'            => round((float) $draft->amount, 2),
            'cbm'               => round((float) $draft->cbm, 2),
        ] : null;
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('product'), __('products')])
                ->withEmptyState([
                    'title'       => __('No draft purchase order'),
                    'description' => __('Products added to a draft purchase order for this supplier show here until it is submitted'),
                ])
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), sortable: true)
                ->column(key: 'cartons', label: __('Cartons'), sortable: true, align: 'right')
                ->column(key: 'units', label: __('Units'), sortable: true, align: 'right')
                ->column(key: 'unit_cost', label: __('Unit cost'), sortable: true, align: 'right')
                ->column(key: 'amount', label: __('Total'), sortable: true, align: 'right')
                ->column(key: 'cbm', label: __('Volume'), sortable: true, align: 'right')
                ->defaultSort('code');
        };
    }

    public function asController(Organisation $organisation, OrgSupplier $orgSupplier, ActionRequest $request): LengthAwarePaginator
    {
        abort_if($orgSupplier->org_agent_id, 404);

        $this->orgSupplier = $orgSupplier;
        $this->initialisation($organisation, $request);

        return $this->handle($orgSupplier);
    }

    public function htmlResponse(LengthAwarePaginator $lines, ActionRequest $request): Response
    {
        $title = __('Purchase orders to submit');

        return Inertia::render(
            'Procurement/SupplierDraftsToSend',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->orgSupplier, $request->route()->originalParameters()),
                'title'       => $title,
                'pageHead'    => [
                    'icon'          => ['icon' => ['fal', 'fa-paper-plane'], 'title' => $title],
                    'model'         => $this->orgSupplier->supplier->name,
                    'title'         => $title,
                    'subNavigation' => $this->getOrgSupplierNavigation($this->orgSupplier),
                ],
                'currency'    => $this->orgSupplier->supplier->currency->code,
                'canSend'     => $request->user()->authTo("procurement.{$this->organisation->id}.edit"),
                'draft'       => $this->draftSummary($this->orgSupplier),
                'data'        => $lines,
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(OrgSupplier $orgSupplier, array $routeParameters): array
    {
        return array_merge(
            ShowSupplierShoppingDashboard::make()->getBreadcrumbs($orgSupplier, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.procurement.org_suppliers.show.shopping.drafts.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('POs to submit'),
                        'icon'  => 'fal fa-paper-plane',
                    ],
                ],
            ]
        );
    }
}
