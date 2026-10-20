<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\UI;

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * An agent's suppliers with how complete their products' data is, each opening onto its families.
 */
class IndexAgentSuppliersDataQuality
{
    use AsObject;
    use WithAgentProductFacts;

    public function handle(Agent $agent, Organisation $agentOrganisation, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $totals = DB::query()->fromSub($this->agentProductFacts($agent), 'f')
            ->groupBy('f.supplier_id')
            ->select('f.supplier_id')
            ->selectRaw('count(*) as number_products')
            ->selectRaw('count(distinct f.stock_family_id) as number_families')
            ->selectRaw('count(*) filter (where f.stock_family_id is null) as number_without_family')
            ->selectRaw('count(*) filter (where coalesce(f.cbm, 0) = 0) as number_missing_cbm')
            ->selectRaw('count(*) filter (where f.lead_time_days is null) as number_missing_lead_time')
            ->selectRaw("count(*) filter (where f.availability_status <> 'available') as number_not_available")
            ->selectRaw('round(avg('.$this->completenessSql().')) as completeness');

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) use ($agent) {
            $query->where(function ($query) use ($value, $agent) {
                $query->whereStartWith('suppliers.code', $value)
                    ->orWhereAnyWordStartWith('suppliers.name', $value)
                    ->orWhereIn('suppliers.id', DB::table('supplier_products as sp')
                        ->join('stock_has_supplier_products as shs', 'shs.supplier_product_id', 'sp.id')
                        ->join('stocks as st', 'st.id', 'shs.stock_id')
                        ->leftJoin('stock_families as sf', 'sf.id', 'st.stock_family_id')
                        ->where('sp.agent_id', $agent->id)
                        ->where(fn ($query) => $query->where('st.code', 'ilike', $value.'%')->orWhere('sf.code', 'ilike', $value.'%'))
                        ->select('sp.supplier_id'));
            });
        });

        $focus = AllowedFilter::callback('focus', function ($query, $value) {
            match ($value) {
                'cbm_gaps'       => $query->whereRaw('totals.number_missing_cbm > totals.number_products * 0.5'),
                'lead_time_gaps' => $query->whereRaw('totals.number_missing_lead_time > totals.number_products * 0.2'),
                'no_email'       => $query->whereRaw("coalesce(suppliers.email, '') = ''"),
                'without_family' => $query->where('totals.number_without_family', '>', 0),
                default          => null,
            };
        });

        $bought = DB::table('purchase_order_transactions as pot')
            ->join('purchase_orders as po', 'po.id', 'pot.purchase_order_id')
            ->join('supplier_products as sp', 'sp.id', 'pot.supplier_product_id')
            ->where('sp.agent_id', $agent->id)
            ->whereNull('pot.deleted_at')
            ->whereNotIn('pot.state', [PurchaseOrderTransactionStateEnum::IN_PROCESS->value, PurchaseOrderTransactionStateEnum::CANCELLED->value])
            ->where('po.submitted_at', '>=', now()->subYear())
            ->groupBy('sp.supplier_id')
            ->select('sp.supplier_id')
            ->selectRaw('sum(pot.grp_net_amount) as bought_amount')
            ->selectRaw('count(distinct po.id) as number_purchase_orders');

        $paginator = QueryBuilder::for(Supplier::class)
            ->joinSub($totals, 'totals', 'totals.supplier_id', 'suppliers.id')
            ->leftJoinSub($bought, 'bought', 'bought.supplier_id', 'suppliers.id')
            ->select([
                'suppliers.id',
                'suppliers.code',
                'suppliers.name',
                'suppliers.contact_name',
                'suppliers.phone',
                'suppliers.email',
                'suppliers.wechat',
                'suppliers.whatsapp',
                'totals.number_products',
                'totals.number_families',
                'totals.number_without_family',
                'totals.number_missing_cbm',
                'totals.number_missing_lead_time',
                'totals.number_not_available',
                'totals.completeness',
            ])
            ->selectRaw('coalesce(bought.bought_amount, 0) as bought_amount')
            ->selectRaw('coalesce(bought.number_purchase_orders, 0) as number_purchase_orders')
            ->defaultSort('-number_products')
            ->allowedSorts(['code', 'name', 'number_products', 'number_missing_cbm', 'number_missing_lead_time', 'completeness', 'bought_amount'])
            ->allowedFilters([$globalSearch, $focus])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();

        $families = $this->families($agent, $paginator->getCollection()->pluck('id')->all());
        $currencyCode = $agentOrganisation->group->currency->code;
        $paginator->getCollection()->each(function (Supplier $supplier) use ($families, $agentOrganisation, $currencyCode) {
            $supplier->setAttribute('currency_code', $currencyCode);
            $supplier->setAttribute('families', $families->get($supplier->id, collect())->values()->map(function ($family) use ($agentOrganisation, $supplier) {
                $family->update_route = $family->stock_family_id ? [
                    'name'       => 'grp.models.org.agent_supplier.family.update',
                    'parameters' => ['organisation' => $agentOrganisation->id, 'supplier' => $supplier->id, 'stockFamily' => $family->stock_family_id],
                ] : null;

                return $family;
            })->all());
            $supplier->setAttribute('contacts_update_route', [
                'name'       => 'grp.models.org.agent_supplier.contacts.update',
                'parameters' => ['organisation' => $agentOrganisation->id, 'supplier' => $supplier->id],
            ]);
        });

        return $paginator;
    }

    /**
     * @param array<int, int> $supplierIds
     */
    private function families(Agent $agent, array $supplierIds): Collection
    {
        if (!$supplierIds) {
            return collect();
        }

        return DB::query()->fromSub($this->agentProductFacts($agent)->whereIn('sp.supplier_id', $supplierIds), 'f')
            ->leftJoin('stock_families as sf', 'sf.id', 'f.stock_family_id')
            ->leftJoin('supplier_stock_families as ssf', function ($join) {
                $join->on('ssf.supplier_id', 'f.supplier_id')->on('ssf.stock_family_id', 'f.stock_family_id');
            })
            ->groupBy('f.supplier_id', 'f.stock_family_id', 'sf.code', 'sf.name', 'ssf.id')
            ->orderByRaw('sf.code nulls last')
            ->select(['f.supplier_id', 'f.stock_family_id', 'sf.code', 'sf.name', 'ssf.minimum_carton_order', 'ssf.estimated_lead_time_days', 'ssf.packaging_terms', 'ssf.production_calendar'])
            ->selectRaw('count(*) as number_products')
            ->selectRaw('count(*) filter (where coalesce(f.cbm, 0) = 0) as number_missing_cbm')
            ->selectRaw('count(*) filter (where f.lead_time_days is null) as number_missing_lead_time')
            ->selectRaw("count(*) filter (where f.availability_status <> 'available') as number_not_available")
            ->selectRaw('round(avg('.$this->completenessSql().')) as completeness')
            ->get()
            ->groupBy('supplier_id');
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch(__('Supplier, internal code or family'))
                ->withLabelRecord([__('Supplier'), __('Suppliers')])
                ->selectFilter('focus', [
                    'cbm_gaps'       => __('CBM missing on over half'),
                    'lead_time_gaps' => __('Lead time missing on over 20%'),
                    'no_email'       => __('No email'),
                    'without_family' => __('Products without family'),
                ], __('Show'), noFilterOptionLabel: __('All suppliers'))
                ->column(key: 'code', label: __('Supplier'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_products', label: __('Products'), canBeHidden: false, sortable: true, type: 'number')
                ->column(key: 'bought_amount', label: __('Bought, last 12 months'), canBeHidden: false, sortable: true, type: 'currency')
                ->column(key: 'number_families', label: __('Families'), canBeHidden: false, type: 'number')
                ->column(key: 'number_missing_cbm', label: __('Missing CBM'), sortable: true, type: 'number')
                ->column(key: 'number_missing_lead_time', label: __('Missing lead time'), sortable: true, type: 'number')
                ->column(key: 'number_not_available', label: __('Not available'), type: 'number')
                ->column(key: 'contacts', label: __('Contacts'), canBeHidden: false)
                ->column(key: 'completeness', label: __('Complete'), canBeHidden: false, sortable: true, type: 'number')
                ->defaultSort('-number_products');
        };
    }
}
