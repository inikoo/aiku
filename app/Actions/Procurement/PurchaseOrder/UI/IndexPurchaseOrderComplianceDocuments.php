<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Enums\Goods\TradeUnit\TradeAttachmentScopeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Goods\TradeUnit;
use App\Models\Procurement\PurchaseOrder;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * The products of a purchase order, one row per trade unit, with the compliance documents each has.
 */
class IndexPurchaseOrderComplianceDocuments
{
    use AsObject;

    public function handle(PurchaseOrder $purchaseOrder, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(fn ($query) => $query
                ->where('trade_units.code', 'ILIKE', "%$value%")
                ->orWhere('trade_units.name', 'ILIKE', "%$value%"));
        });

        $orgStockIds = DB::table('purchase_order_transactions')
            ->where('purchase_order_id', $purchaseOrder->id)
            ->where('state', '!=', 'cancelled')
            ->select('org_stock_id');

        return QueryBuilder::for(TradeUnit::class)
            ->whereIn('trade_units.id', DB::table('model_has_trade_units')
                ->where('model_type', 'OrgStock')
                ->whereIn('model_id', $orgStockIds)
                ->select('trade_unit_id'))
            ->with(['attachments', 'documentsTradeUnit.attachments'])
            ->select('trade_units.*')
            ->defaultSort('trade_units.code')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(['title' => __('No products on this order yet')])
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), sortable: true);

            foreach (GetPurchaseOrderCompliance::DOCUMENTS as $scope) {
                $table->column(key: $scope->value, label: TradeAttachmentScopeEnum::labels()[$scope->value], align: 'center');
            }
        };
    }
}
