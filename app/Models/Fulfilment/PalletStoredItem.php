<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 11:03:38 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletStoredItem\PalletStoredItemStateEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * App\Models\Fulfilment\PalletStoredItem
 *
 * @property int $id
 * @property int $pallet_id
 * @property int $stored_item_id
 * @property numeric $quantity
 * @property numeric $damaged_quantity
 * @property string $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $number_audits
 * @property string|null $last_audit_at
 * @property int|null $last_stored_item_audit_delta_id
 * @property int|null $last_stored_item_audit_id
 * @property bool $in_process
 * @property PalletStoredItemStateEnum $state
 * @property numeric|null $delivered_quantity
 * @property-read \App\Models\Fulfilment\Pallet|null $pallet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletReturnItem> $palletReturnItems
 * @property-read \App\Models\Fulfilment\StoredItem $storedItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PalletStoredItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PalletStoredItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PalletStoredItem query()
 * @mixin \Eloquent
 */
class PalletStoredItem extends Model
{
    protected $guarded = [];
    protected $casts = [
        'state'                   => PalletStoredItemStateEnum::class,
    ];

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class);
    }

    public function storedItem(): BelongsTo
    {
        return $this->belongsTo(StoredItem::class);
    }

    public function palletReturnItems(): HasMany
    {
        return $this->hasMany(PalletReturnItem::class);
    }

    /**
     * Lines of open returns that still have stock to pick from this pallet.
     */
    public function reservingPalletReturnItems(): HasMany
    {
        return $this->hasMany(PalletReturnItem::class)
            ->whereHas('palletReturn', fn (Builder $query) => $query->whereIn('state', [
                PalletReturnStateEnum::IN_PROCESS,
                PalletReturnStateEnum::SUBMITTED,
                PalletReturnStateEnum::CONFIRMED,
                PalletReturnStateEnum::PICKING,
            ]))
            ->whereRaw('pallet_return_items.quantity_ordered - COALESCE(pallet_return_items.quantity_picked, 0) - COALESCE(pallet_return_items.quantity_not_picked, 0) > 0');
    }

    /**
     * Reads the eager loaded reservingPalletReturnItems.palletReturn, so listings stay free of per row queries.
     *
     * @return array{quantity_in_pallet: int, reserved_quantity: int, available_quantity: int, reserved_in_other_returns: Collection<int, array{id: int, reference: string, slug: string, state_icon: array<string, string>, quantity: int}>}
     */
    public function getStockReservations(?int $exceptPalletReturnId = null): array
    {
        $reservedInOtherReturns = $this->reservingPalletReturnItems
            ->where('pallet_return_id', '!=', $exceptPalletReturnId)
            ->map(fn (PalletReturnItem $palletReturnItem) => [
                'id'         => $palletReturnItem->pallet_return_id,
                'reference'  => $palletReturnItem->palletReturn->reference,
                'slug'       => $palletReturnItem->palletReturn->slug,
                'state_icon' => PalletReturnStateEnum::stateIcon()[$palletReturnItem->palletReturn->state->value],
                'quantity'   => (int) $palletReturnItem->getQuantityLeftToPick(),
            ])
            ->values();
        $reservedQuantity = (float) $reservedInOtherReturns->sum('quantity');

        return [
            'quantity_in_pallet'        => (int) $this->quantity,
            'reserved_quantity'         => (int) $reservedQuantity,
            'available_quantity'        => (int) max(0, (float) $this->quantity - $reservedQuantity),
            'reserved_in_other_returns' => $reservedInOtherReturns,
        ];
    }

    public function getQuantityReservedInOtherReturns(?int $palletReturnId): float
    {
        return (float) $this->reservingPalletReturnItems()
            ->when($palletReturnId, fn (Builder $query) => $query->where('pallet_return_id', '!=', $palletReturnId))
            ->get()
            ->sum(fn (PalletReturnItem $palletReturnItem) => $palletReturnItem->getQuantityLeftToPick());
    }

    /**
     * Stock a return may still take from this pallet: what is on it minus what other open returns still have to pick.
     * A return can always keep or lower the quantity it already holds.
     */
    public function getMaxQuantityForReturn(PalletReturn $palletReturn): float
    {
        $quantityAlreadyInReturn = (float) $this->palletReturnItems()
            ->where('pallet_return_id', $palletReturn->id)
            ->value('quantity_ordered');

        return self::calculateMaxQuantityForReturn(
            (float) $this->quantity,
            $this->getQuantityReservedInOtherReturns($palletReturn->id),
            $quantityAlreadyInReturn
        );
    }

    public static function calculateMaxQuantityForReturn(float $quantityInPallet, float $quantityReservedInOtherReturns, float $quantityAlreadyInReturn): float
    {
        return max($quantityInPallet - $quantityReservedInOtherReturns, $quantityAlreadyInReturn, 0);
    }
}
