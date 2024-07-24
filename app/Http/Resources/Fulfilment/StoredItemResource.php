<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $reference
 * @property string $state
 * @property string $status
 * @property string $notes
 * @property \App\Models\CRM\Customer $customer
 * @property \App\Models\Inventory\Location $location
 */
class StoredItemResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\StoredItem $storedItem */
        $storedItem = $this;

        return [
            'id'            => $storedItem->id,
            'reference'     => $storedItem->reference,
            'slug'          => $storedItem->slug,
            'customer_name' => $storedItem->fulfilmentCustomer?->customer['name'],
            'location'      => $storedItem->location ? $storedItem->location['slug'] : '-',
            'state'         => $storedItem->state,
            'notes'         => $storedItem->notes ?? '-',
            'status'        => $storedItem->status,
            'quantity'      => (int) $storedItem->pallets?->pivot->quantity,
            'total_quantity'=> $storedItem->pallets?->sum('pivot.quantity'),
            'max_quantity'  => $storedItem->pallets?->sum('pivot.quantity'),
            'pallet_name'   => $storedItem->pallets()->pluck('reference')->implode(', '),
            'deleteRoute'   => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.stored-item.delete',
                    'parameters' => [
                        'storedItem' => $storedItem->id
                    ]
                ],
                default => [
                    'name'       => 'grp.models.fulfilment-customer.stored-item-return.stored-item.delete',
                    'parameters' => [
                        'fulfilmentCustomer' => $storedItem->fulfilmentCustomer->id,
                        'storedItemReturn'   => $storedItem->id
                    ]
                ]
            }
        ];
    }
}
