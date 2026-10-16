<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\AgentGoodsReceipt\AgentGoodsReceiptStateEnum;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Models\Helpers\Media;
use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\Procurement\AgentGoodsReceiptItem;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * One goods receipt: what the supplier delivered, line by line with the rejects found when checked, and the photos of
 * its signed delivery note.
 */
class ShowAgentGoodsReceipt extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;
    use WithAgentGoodsReceiptBreadcrumbs;

    public function handle(AgentGoodsReceipt $agentGoodsReceipt): AgentGoodsReceipt
    {
        $agentGoodsReceipt->load([
            'supplier:id,code,name',
            'user:id,contact_name,username',
            'cancelledBy:id,contact_name,username',
            'items.supplierProduct:id,code,name,units_per_pack',
            'items.organisation:id,code,slug',
            'items.purchaseOrder:id,reference,slug',
            'items.purchaseOrderTransaction:id,quantity_ordered',
            'items.agentWarehouseStock:id,state,quantity',
            'items.checkedBy:id,contact_name,username',
            'items.attachments',
            'attachments',
        ]);
        $agentGoodsReceipt->items->each->setRelation('agentGoodsReceipt', $agentGoodsReceipt);

        return $agentGoodsReceipt;
    }

    public function asController(Organisation $organisation, AgentGoodsReceipt $agentGoodsReceipt, ActionRequest $request): AgentGoodsReceipt
    {
        abort_unless($agentGoodsReceipt->agent_id === $this->getOrganisationAgent($organisation)?->id, 404);
        $this->initialisation($organisation, $request);

        return $this->handle($agentGoodsReceipt);
    }

    private function canBeCancelled(AgentGoodsReceipt $receipt): bool
    {
        return $receipt->state === AgentGoodsReceiptStateEnum::RECEIVED
            && $receipt->items->every(fn (AgentGoodsReceiptItem $item) => $item->agentWarehouseStock?->state === AgentWarehouseStockStateEnum::RECEIVED);
    }

    public function htmlResponse(AgentGoodsReceipt $receipt, ActionRequest $request): Response
    {
        $parameters = [$this->organisation->slug, $receipt->slug];
        $person     = fn ($user) => $user ? ($user->contact_name ?: $user->username) : null;

        return Inertia::render(
            'Org/Procurement/AgentGoodsReceipt',
            [
                'title'       => $receipt->reference,
                'breadcrumbs' => $this->goodsReceiptBreadcrumbs($request, $receipt),
                'pageHead'    => [
                    'model' => __('Goods receipt'),
                    'icon'  => ['title' => __('Goods receipt'), 'icon' => 'fal fa-clipboard-check'],
                    'title' => $receipt->reference,
                ],
                'receipt'     => [
                    'reference'              => $receipt->reference,
                    'state'                  => $receipt->state->value,
                    'state_label'            => $receipt->state->label(),
                    'received_at'            => $receipt->received_at->toDateString(),
                    'received_by'            => $person($receipt->user),
                    'supplier_code'          => $receipt->supplier?->code,
                    'supplier_name'          => $receipt->supplier?->name,
                    'supplier_delivery_note' => $receipt->supplier_delivery_note,
                    'notes'                  => $receipt->notes,
                    'number_items'           => $receipt->number_items,
                    'quantity'               => (float) $receipt->quantity,
                    'cancelled_at'           => $receipt->cancelled_at?->toIso8601String(),
                    'cancelled_by'           => $person($receipt->cancelledBy),
                ],
                'items'       => $receipt->items->map(fn (AgentGoodsReceiptItem $item) => [
                    'id'                       => $item->id,
                    'code'                     => $item->supplierProduct?->code,
                    'name'                     => $item->supplierProduct?->name,
                    'organisation_code'        => strtoupper((string) $item->organisation?->code),
                    'purchase_order_reference' => $item->purchaseOrder?->reference,
                    'purchase_order_slug'      => $item->purchaseOrder?->slug,
                    'quantity_ordered'         => (float) $item->purchaseOrderTransaction?->quantity_ordered,
                    'quantity'                 => (float) $item->quantity,
                    'quantity_rejected'        => $item->quantity_rejected === null ? null : (float) $item->quantity_rejected,
                    'is_checked'               => $item->isChecked(),
                    'reject_reason'            => $item->reject_reason,
                    'checked_at'               => $item->checked_at?->toIso8601String(),
                    'checked_by'               => $person($item->checkedBy),
                    'is_packed'                => $item->agentWarehouseStock !== null && $item->agentWarehouseStock->state !== AgentWarehouseStockStateEnum::RECEIVED,
                    'units_per_sko'            => (float) ($item->supplierProduct?->units_per_pack ?: 1),
                    'photos'                   => $item->attachments->map(fn (Media $media) => [
                        'id'   => $media->id,
                        'name' => $media->file_name,
                        'url'  => route('grp.media.download', [$media->ulid, 'inline' => 1]),
                    ])->values(),
                    'check_route'              => $this->canEdit && $item->isEditable() ? [
                        'name'       => 'grp.org.agent.warehouse.received.goods_receipts.items.check',
                        'parameters' => [...$parameters, $item->id],
                    ] : null,
                ])->values(),
                'photos'      => $receipt->attachments->map(fn (Media $media) => [
                    'id'           => $media->id,
                    'caption'      => $media->pivot->caption,
                    'file_name'    => $media->file_name,
                    'is_image'     => str_starts_with((string) $media->mime_type, 'image/'),
                    'url'          => route('grp.media.download', [$media->ulid, 'inline' => 1]),
                    'download_url' => route('grp.media.download', [$media->ulid]),
                    'delete_route' => $this->canEdit ? [
                        'name'       => 'grp.org.agent.warehouse.received.goods_receipts.photos.destroy',
                        'parameters' => [...$parameters, $media->id],
                    ] : null,
                ])->values(),
                'upload_route' => $this->canEdit ? [
                    'name'       => 'grp.org.agent.warehouse.received.goods_receipts.photos.store',
                    'parameters' => $parameters,
                ] : null,
                'receipt_note_route' => [
                    'name'       => 'grp.org.agent.warehouse.received.goods_receipts.receipt_note',
                    'parameters' => $parameters,
                ],
                'cancel_route' => $this->canEdit && $this->canBeCancelled($receipt) ? [
                    'name'       => 'grp.org.agent.warehouse.received.goods_receipts.cancel',
                    'parameters' => $parameters,
                ] : null,
            ]
        );
    }
}
