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
        // dd($storedItem);
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
            'pallet_stored_items'              => $storedItem->palletStoredItems->map(fn ($palletStoredItem) => [
                'id'        => $palletStoredItem->id,
                'reference' => $palletStoredItem->pallet->reference,
                'selected_quantity'     => (int) 0,  // TODO Kirin
                'available_quantity'    => (int) $palletStoredItem->quantity,
                'max_quantity'          => (int) $palletStoredItem->quantity,
                'pallet_return_item_id' => $palletStoredItem->palletReturnItem->id ?? null,
                'updateRoute' => $palletStoredItem->palletReturnItem
                ? [
                    'name'       => 'grp.models.pallet-return-item.update',
                    'parameters' => [$palletStoredItem->palletReturnItem->id]
                ]
                : null,
                'location' => [
                    'slug'   => $palletStoredItem->pallet->location->slug,
                    'code'   => $palletStoredItem->pallet->location->code
                ]
            ]),
            'total_quantity' => (int)$this->total_quantity,
            // 'syncRoute'             => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet.pallet-return-item.update',
            //         'parameters' => $this->id
            //     ],
            //     default => [
            //         'name'       => 'grp.models.pallet.pallet-return-item.sync',
            //         'parameters' => $this->id
            //     ]
            // },
            // 'updateRoute'           => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet-return-item.update',
            //         'parameters' => $this->id
            //     ],
            //     default => [
            //         'name'       => 'grp.models.pallet-return-item.update',
            //         'parameters' => $this->id
            //     ]
            // },
            // 'undoPickingRoute' => [
            //     'name'       => 'grp.models.pallet-return-item.undo-picking',
            //     'parameters' => [$this->id]
            // ],
            // 'undoConfirmedRoute' => [
            //     'name'       => 'grp.models.pallet-return-item.undo-confirmed',
            //     'parameters' => [$this->id]
            // ],
            // 'notPickedRoute' => [
            //     'method'     => 'patch',
            //     'name'       => 'grp.models.pallet-return-item.not-picked',
            //     'parameters' => [$this->id]
            // ],
            // 'deleteRoute' => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet.delete',
            //         'parameters' => $this->id
            //     ],
            //     default => [
            //         'name'       => 'grp.models.pallet.delete',
            //         'parameters' => $this->id
            //     ]
            // },
            // 'deleteFromDeliveryRoute' => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet-delivery.pallet.delete',
            //         'parameters' => [$this->pallet_delivery_id, $this->id]
            //     ],
            //     default => [
            //         'name'       => 'grp.models.fulfilment-customer.pallet-delivery.pallet.delete',
            //         'parameters' => [$this->fulfilment_customer_id, $this->pallet_delivery_id, $this->id]
            //     ]
            // },
            // 'deleteFromReturnRoute' => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet-return.pallet.delete',
            //         'parameters' => [$this->pallet_return_id, $this->pallet_id]
            //     ],
            //     default => [
            //         'name'       => 'grp.models.fulfilment-customer.pallet-return.pallet.delete',
            //         'parameters' => [$this->fulfilment_customer_id, $this->pallet_return_id, $this->pallet_id]
            //     ]
            // },
            // 'notReceivedRoute' => [
            //     'name'       => 'grp.models.pallet.not-received',
            //     'parameters' => [$this->id]
            // ],
            // 'undoNotReceivedRoute' => [
            //     'name'       => 'grp.models.pallet.undo-not-received',
            //     'parameters' => [$this->id]
            // ],
            // 'bookInRoute' => [
            //     'name'       => 'grp.models.pallet.book_in',
            //     'parameters' => [$this->id]
            // ],
            // 'undoBookInRoute' => [
            //     'name'       => 'grp.models.pallet.undo_book_in',
            //     'parameters' => [$this->id]
            // ],
            // 'updateLocationRoute' => [
            //     'name'       => 'grp.models.warehouse.pallets.location.update',
            //     'parameters' => [$this->warehouse_id, $this->id]
            // ],
            // 'storeStoredItemRoute' => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet.stored-items.update',
            //         'parameters' => [$this->id]
            //     ],
            //     default => [
            //         'name'       => 'grp.models.pallet.stored-items.update',
            //         'parameters' => [$this->id]
            //     ]
            // },
            // 'updatePalletRoute'           => match (request()->routeIs('retina.*')) {
            //     true => [
            //         'name'       => 'retina.models.pallet.update',
            //         'parameters' => $this->pallet_id
            //     ],
            //     default => [
            //         'name'       => 'grp.models.pallet.update',
            //         'parameters' => $this->pallet_id
            //     ]
            // },
        ];
    }
}
