<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:58
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt\UI;

use App\Actions\Procurement\AgentWarehouseReceipt\UpdateAgentWarehouseReceipt;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Models\Helpers\Media;
use App\Models\Procurement\AgentWarehouseReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What the agent has received of each line of a purchase order into its own warehouse, batch by batch, and what the
 * supplier still owes: ordered less received, plus whatever was rejected.
 */
class GetPurchaseOrderAgentReceiving
{
    use AsObject;

    public function handle(PurchaseOrder $purchaseOrder, bool $canEdit): ?array
    {
        $receipts = AgentWarehouseReceipt::where('purchase_order_id', $purchaseOrder->id)
            ->with(['agentWarehouseStock', 'receivedBy:id,contact_name,username', 'checkedBy:id,contact_name,username', 'attachments'])
            ->orderBy('received_at')
            ->orderBy('id')
            ->get()
            ->groupBy('purchase_order_transaction_id');

        $isOpen = in_array($purchaseOrder->state, [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED], true);

        if (!$isOpen && $receipts->isEmpty()) {
            return null;
        }

        $lines = $purchaseOrder->purchaseOrderTransactions()
            ->whereNotNull('supplier_product_id')
            ->with(['supplierProduct:id,code,name,units_per_pack', 'orgStock.tradeUnits.image'])
            ->get()
            ->sortBy(fn (PurchaseOrderTransaction $transaction) => $transaction->supplierProduct?->code)
            ->map(fn (PurchaseOrderTransaction $transaction) => $this->line($transaction, $receipts->get($transaction->id, collect()), $isOpen, $canEdit))
            ->values();

        return [
            'can_edit' => $canEdit,
            'lines'    => $lines,
        ];
    }

    private function line(PurchaseOrderTransaction $transaction, Collection $receipts, bool $isOpen, bool $canEdit): array
    {
        $ordered  = (float) $transaction->quantity_ordered;
        $received = (float) $receipts->sum('quantity_received');
        $rejected = (float) $receipts->sum('quantity_rejected');
        $accepted = $received - $rejected;
        $tradeUnit = $transaction->orgStock?->tradeUnits->first(fn ($tradeUnit) => $tradeUnit->image_id !== null);
        $isLive   = in_array($transaction->state, [PurchaseOrderTransactionStateEnum::SUBMITTED, PurchaseOrderTransactionStateEnum::CONFIRMED], true);

        return [
            'id'              => $transaction->id,
            'code'            => $transaction->supplierProduct?->code,
            'name'            => $transaction->supplierProduct?->name,
            'image_thumbnail' => $tradeUnit?->imageSources(80, 80),
            'image_full'      => $tradeUnit?->image?->fullResolutionUrls(),
            'state'           => $transaction->state->value,
            'units_per_pack'  => (float) ($transaction->supplierProduct?->units_per_pack ?: 1),
            'ordered'         => $ordered,
            'received'        => $received,
            'rejected'        => $rejected,
            'accepted'        => $accepted,
            'outstanding'     => $isLive ? max($ordered - $accepted, 0) : 0,
            'unchecked'       => $receipts->filter(fn (AgentWarehouseReceipt $receipt) => !$receipt->isChecked())->count(),
            'store_route'     => $canEdit && $isOpen && $isLive && $ordered > $accepted ? [
                'name'       => 'grp.models.purchase-order-transaction.agent_warehouse_receipt.store',
                'parameters' => [$transaction->id],
            ] : null,
            'receipts'        => $receipts->map(fn (AgentWarehouseReceipt $receipt) => $this->receipt($receipt, $canEdit))->values(),
        ];
    }

    private function receipt(AgentWarehouseReceipt $receipt, bool $canEdit): array
    {
        $isEditable = $canEdit && $receipt->isEditable();

        return [
            'id'                => $receipt->id,
            'received_at'       => $receipt->received_at->toDateString(),
            'quantity_received' => (float) $receipt->quantity_received,
            'quantity_rejected' => $receipt->quantity_rejected === null ? null : (float) $receipt->quantity_rejected,
            'is_checked'        => $receipt->isChecked(),
            'checked_at'        => $receipt->checked_at,
            'reject_reason'     => $receipt->reject_reason,
            'received_by'       => $receipt->receivedBy?->getPasskeyDisplayName(),
            'checked_by'        => $receipt->checkedBy?->getPasskeyDisplayName(),
            'is_packed'         => !$receipt->isEditable(),
            'photos'            => $receipt->attachments
                ->where('pivot.scope', UpdateAgentWarehouseReceipt::PHOTO_SCOPE)
                ->map(fn (Media $media) => [
                    'id'   => $media->id,
                    'name' => $media->name,
                    'url'  => route('grp.media.download', [$media->ulid]),
                ])->values(),
            'update_route'      => $isEditable ? [
                'name'       => 'grp.models.agent_warehouse_receipt.update',
                'parameters' => [$receipt->id],
            ] : null,
            'delete_route'      => $isEditable ? [
                'name'       => 'grp.models.agent_warehouse_receipt.delete',
                'parameters' => [$receipt->id],
                'method'     => 'delete',
            ] : null,
        ];
    }
}
