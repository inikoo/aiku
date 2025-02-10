<?php

/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-13h-43m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $reference
 * @property mixed $customer_reference
 * @property mixed $fulfilment_customer_id
 * @property mixed $slug
 * @property mixed $notes
 * @property mixed $state
 * @property mixed $type
 * @property mixed $pivot
 * @property mixed $status
 * @property mixed $location_slug
 * @property mixed $location_code
 * @property mixed $location_id
 * @property mixed $warehouse_id
 * @property mixed $pallet_delivery_id
 * @property mixed $pallet_return_id
 * @property mixed $pallet_id
 * @property mixed $fulfilment_customer_name
 * @property mixed $fulfilment_customer_slug
 */
class PalletReturnItemsWithStoredItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        $storedItem = StoredItem::find($this->id);
        // dump($this->palletReturns);
        return [
            'id'                               => $this->id,
            'slug'                             => $this->slug,
            'reference'                        => $this->reference,
            'name'                             => $this->name,
            'total_quantity_ordered'           => (int)$this->total_quantity_ordered ?? 0,
            // 'customer_reference'               => (string)$this->customer_reference,
            // 'fulfilment_customer_name'         => $this->fulfilment_customer_name,
            // 'fulfilment_customer_slug'         => $this->fulfilment_customer_slug,
            // 'fulfilment_customer_id'           => $this->fulfilment_customer_id,
            // 'notes'                            => (string)$this->notes,
            // 'state'                            => $this->state->value,
            // 'type_icon'                        => $this->type->typeIcon()[$this->type->value],
            // 'type'                             => $this->type,
            // 'state_label'                      => PalletStateEnum::labels()[$this->state->value],
            // 'state_icon'                       => PalletStateEnum::stateIcon()[$this->state->value],
            // 'status'                           => $this->status,
            // 'status_label'                     => $this->status->labels()[$this->status->value],
            // 'status_icon'                      => $this->status->statusIcon()[$this->status->value],
            // 'location'                         => $this->location_slug,
            // 'location_code'                    => $this->location_code,
            // 'location_id'                      => $this->location_id,
            'is_checked'                       => (bool) $this->pallet_return_id,
            'pallet_return_state'              => $this->pallet_return_state ?? null,
            'pallet_stored_items'              => $storedItem->palletStoredItems->map(fn ($palletStoredItem) => [
                'id'                            => $palletStoredItem->id,
                'reference'                     => $palletStoredItem->pallet->reference ?? null,
                'selected_quantity'             => (int) optional(
                    $palletStoredItem->palletReturnItems
                        ->where('pallet_return_id', $this->pallet_return_id)
                        ->first()
                )->quantity_ordered ?? 0,
                'available_quantity'            => (int) $palletStoredItem->quantity,
                'max_quantity'                  => (int) $palletStoredItem->quantity,
                'available_to_pick_quantity'    => (int) optional(
                    $palletStoredItem->palletReturnItems
                        ->where('pallet_return_id', $this->pallet_return_id)
                        ->first()
                )->quantity_ordered ?? 0,
                'picked_quantity'               => (int) optional(
                    $palletStoredItem->palletReturnItems
                        ->where('pallet_return_id', $this->pallet_return_id)
                        ->first()
                )->quantity_picked ?? 0,
                'pallet_return_item_id'         => optional(
                    $palletStoredItem->palletReturnItems
                        ->where('pallet_return_id', $this->pallet_return_id)
                        ->first()
                )->id ?? null,
                'syncRoute' =>
                    [
                        'name'       => 'grp.models.pallet-return.stored_item.store',
                        'parameters' => [
                            'palletReturn'       => $this->pallet_return_id,
                            'palletStoredItem'   => $palletStoredItem->id
                        ],
                        'method'    => 'post'
                    ],
                'location' => isset($palletStoredItem->pallet, $palletStoredItem->pallet->location) ? [
                    'slug' => $palletStoredItem->pallet->location->slug ?? null,
                    'code' => $palletStoredItem->pallet->location->code ?? null
                ] : null
            ]),
            'total_quantity' => (int)$this->total_quantity,
        ];
    }
}
