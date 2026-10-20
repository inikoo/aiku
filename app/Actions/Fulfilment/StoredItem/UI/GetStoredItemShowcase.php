<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemMovement;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStoredItemShowcase
{
    use AsObject;

    public function handle(StoredItem $storedItem): array
    {

        $lastAudit = $storedItem->fulfilmentCustomer->storedItemAudits->last();
        $palletStoredItems = $storedItem->palletStoredItems()
            ->with('reservingPalletReturnItems.palletReturn')
            ->get()
            ->keyBy('pallet_id');
        $lastPicks = $this->lastPickLocations($storedItem);

        return [
            'stored_item'         => StoredItemResource::make($storedItem)->getArray(),
            'barcode'             => $storedItem->barcode,
            'barcode_update_route' => [
                'name'       => 'grp.models.stored-items.update',
                'parameters' => [
                    'storedItem' => $storedItem->id
                ],
                'method'     => 'patch'
            ],
            'pallets'             => $storedItem->pallets->map(function (Pallet $pallet) use ($palletStoredItems, $lastPicks) {
                return [
                    'id'          => $pallet->id,
                    'slug'        => $pallet->slug,
                    'reference'   => $pallet->reference,
                    'state'       => $pallet->state,
                    'state_label' => $pallet->state->labels()[$pallet->state->value],
                    'state_icon'  => $pallet->state->stateIcon()[$pallet->state->value],
                    'location'    => [
                        'id'     => $pallet->location?->id,
                        'code'   => $pallet->location?->code,
                        'slug'   => $pallet->location?->slug,
                    ],
                    'last_picked_from' => $pallet->location_id ? null : $lastPicks->get($pallet->id),
                    'quantity' => (int) $pallet->pivot->quantity,
                    'reserved_quantity'         => 0,
                    'available_quantity'        => (int) $pallet->pivot->quantity,
                    'reserved_in_other_returns' => [],
                    ...($palletStoredItems->get($pallet->id)?->getStockReservations() ?? []),
                ];
            }),
            'pieData'             => $this->getDashboardData($storedItem),
            'route_pallets'       => [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                'parameters' => [
                    'organisation'         => $storedItem->organisation->slug,
                    'fulfilment'           => $storedItem->fulfilment->slug,
                    'fulfilmentCustomer'   => $storedItem->fulfilmentCustomer->slug
                ]
            ],
            'last_audit_slug'         => $lastAudit?->slug,
            'route_update_stored_item' => [
                'name'       => 'grp.models.stored-items.pallets.update',
                'parameters' => [
                    'storedItem' => $storedItem->id
                ]
            ]
        ];
    }

    /**
     * Where this SKU was last picked from on each of its pallets, to show where a pallet that has
     * left the warehouse went out from; the Movements tab lists the same picks.
     *
     * @return Collection<int, array{code: string, moved_at: string|null}>
     */
    private function lastPickLocations(StoredItem $storedItem): Collection
    {
        return StoredItemMovement::query()
            ->where('stored_item_movements.stored_item_id', $storedItem->id)
            ->where('stored_item_movements.type', StoredItemMovementTypeEnum::PICKED->value)
            ->whereNotNull('stored_item_movements.location_id')
            ->join('locations', 'locations.id', '=', 'stored_item_movements.location_id')
            ->orderBy('stored_item_movements.pallet_id')
            ->orderByDesc('stored_item_movements.moved_at')
            ->orderByDesc('stored_item_movements.id')
            ->get(['stored_item_movements.pallet_id', 'stored_item_movements.moved_at', 'locations.code'])
            ->unique('pallet_id')
            ->mapWithKeys(fn (StoredItemMovement $movement) => [
                $movement->pallet_id => [
                    'code'     => $movement->code,
                    'moved_at' => $movement->moved_at?->toIso8601String(),
                ],
            ]);
    }

    public function getDashboardData(StoredItem $parent): array
    {


        $stats          = [];
        $stats['stats'] = $parent->pallets->map(function (Pallet $pallet) {
            return [
                'label' => $pallet->reference,
                'value' => (int) $pallet->pivot->quantity
            ];
        });

        return $stats;
    }
}
