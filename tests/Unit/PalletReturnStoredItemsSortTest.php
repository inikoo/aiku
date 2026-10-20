<?php

namespace Tests\Unit;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletReturnItemsWithStoredItemsResource;

function palletStoredItemRow(int $id, string $reference, string $locationCode, int $quantityInPallet, int $availableQuantity): array
{
    return [
        'id'                 => $id,
        'reference'          => $reference,
        'quantity_in_pallet' => $quantityInPallet,
        'available_quantity' => $availableQuantity,
        'location'           => ['code' => $locationCode],
    ];
}

function palletStoredItemRows(): \Illuminate\Support\Collection
{
    return collect([
        palletStoredItemRow(1, 'p1229', '14Y23', 360, 260),
        palletStoredItemRow(2, 'p1230', '14Y24', 660, 660),
        palletStoredItemRow(3, 'p1243', '16X11', 310, 310),
        palletStoredItemRow(4, 'p1242', '16X4', 60, 0),
        palletStoredItemRow(5, 'p1228', '14Y7', 660, 660),
    ]);
}

test('pallets holding the fewest units of the sku come first while selecting a return', function (string $palletReturnState) {
    $sorted = PalletReturnItemsWithStoredItemsResource::sortPalletStoredItems(palletStoredItemRows(), $palletReturnState);

    expect($sorted->pluck('reference')->all())->toBe(['p1242', 'p1243', 'p1229', 'p1230', 'p1228']);
})->with([
    PalletReturnStateEnum::IN_PROCESS->value,
    PalletReturnStateEnum::SUBMITTED->value,
    PalletReturnStateEnum::CONFIRMED->value,
]);

test('pallets follow the warehouse location order while picking', function (string $palletReturnState) {
    $sorted = PalletReturnItemsWithStoredItemsResource::sortPalletStoredItems(palletStoredItemRows(), $palletReturnState);

    expect($sorted->pluck('reference')->all())->toBe(['p1229', 'p1230', 'p1228', 'p1243', 'p1242']);
})->with([
    PalletReturnStateEnum::PICKING->value,
    PalletReturnStateEnum::PICKED->value,
    PalletReturnStateEnum::DISPATCHED->value,
]);
