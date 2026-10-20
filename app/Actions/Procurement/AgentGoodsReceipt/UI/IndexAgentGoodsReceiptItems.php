<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-17 09:00
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt\UI;

use App\Actions\OrgAction;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Helpers\Media;
use App\Models\Inventory\OrgStock;
use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\Procurement\AgentGoodsReceiptItem;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * The lines of one goods receipt: what the supplier delivered per purchase order line, with the rejects found when
 * each line was checked.
 */
class IndexAgentGoodsReceiptItems extends OrgAction
{
    public function handle(AgentGoodsReceipt $receipt, ?string $prefix = null, bool $canEdit = false): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->where(fn ($query) => $query
                ->where('supplier_products.code', 'ilike', $like)
                ->orWhere('supplier_products.name', 'ilike', $like));
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(AgentGoodsReceiptItem::class)
            ->where('agent_goods_receipt_items.agent_goods_receipt_id', $receipt->id)
            ->leftJoin('supplier_products', 'supplier_products.id', 'agent_goods_receipt_items.supplier_product_id')
            ->leftJoin('purchase_orders', 'purchase_orders.id', 'agent_goods_receipt_items.purchase_order_id')
            ->with([
                'organisation:id,code,slug',
                'purchaseOrder:id,reference,slug',
                'purchaseOrderTransaction:id,quantity_ordered',
                'agentWarehouseStock:id,state,quantity',
                'checkedBy:id,contact_name,username',
                'attachments',
                'supplierProduct:id,code,name',
            ])
            ->select('agent_goods_receipt_items.*')
            ->selectRaw(OrgStock::unitsPerSkoSql('agent_goods_receipt_items.organisation_id', 'agent_goods_receipt_items.supplier_product_id').' as units_per_sko');

        $parameters = [$receipt->agent->organisation->slug, $receipt->slug];
        $person     = fn ($user) => $user ? ($user->contact_name ?: $user->username) : null;

        return $query
            ->defaultSort('supplier_products.code')
            ->allowedSorts([
                AllowedSort::field('code', 'supplier_products.code'),
                AllowedSort::field('purchase_order_reference', 'purchase_orders.reference'),
                AllowedSort::field('quantity', 'agent_goods_receipt_items.quantity'),
            ])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString()
            ->through(function (AgentGoodsReceiptItem $item) use ($receipt, $canEdit, $parameters, $person) {
                $item->setRelation('agentGoodsReceipt', $receipt);

                return [
                    'id'                       => $item->id,
                    'code'                     => $item->supplierProduct?->code,
                    'name'                     => $item->supplierProduct?->name,
                    'organisation_code'        => strtoupper((string) $item->organisation?->code),
                    'purchase_order_reference' => $item->purchaseOrder?->reference,
                    'purchase_order_slug'      => $item->purchaseOrder?->slug,
                    'quantity_ordered'         => (float) $item->purchaseOrderTransaction?->quantity_ordered,
                    'quantity'                 => (float) $item->quantity,
                    'quantity_rejected'        => $item->quantity_rejected === null ? null : (float) $item->quantity_rejected,
                    'has_been_checked'         => $item->isChecked(),
                    'reject_reason'            => $item->reject_reason,
                    'checked_at'               => $item->checked_at?->toIso8601String(),
                    'checked_by'               => $person($item->checkedBy),
                    'is_packed'                => $item->agentWarehouseStock !== null && $item->agentWarehouseStock->state !== AgentWarehouseStockStateEnum::RECEIVED,
                    'units_per_sko'            => (float) ($item->units_per_sko ?? 1),
                    'photos'                   => $item->attachments->map(fn (Media $media) => [
                        'id'   => $media->id,
                        'name' => $media->file_name,
                        'url'  => route('grp.media.download', [$media->ulid, 'inline' => 1]),
                    ])->values(),
                    'check_route'              => $canEdit && $item->isEditable() ? [
                        'name'       => 'grp.org.agent.warehouse.received.goods_receipts.items.check',
                        'parameters' => [...$parameters, $item->id],
                    ] : null,
                ];
            });
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Line'), __('Lines')])
                ->withEmptyState(['title' => __('No lines in this goods receipt')])
                ->column(key: 'organisation_code', label: __('For'), canBeHidden: false)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false)
                ->column(key: 'purchase_order_reference', label: __('Purchase order'), canBeHidden: false, sortable: true)
                ->column(key: 'quantity_ordered', label: __('Ordered'), canBeHidden: false, align: 'right')
                ->column(key: 'quantity', label: __('Received'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'status', label: __('Check'), canBeHidden: false)
                ->column(key: 'action', label: '', canBeHidden: false)
                ->defaultSort('code');
        };
    }
}
