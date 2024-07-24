<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 11:03:38 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fulfilment\PalletStoredItem
 *
 * @property int $id
 * @property int $pallet_id
 * @property int $stored_item_id
 * @property string $quantity
 * @property string $damaged_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\Pallet $pallet
 * @property-read \App\Models\Fulfilment\StoredItem $storedItem
 * @method static \Illuminate\Database\Eloquent\Builder|PalletStoredItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletStoredItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletStoredItem query()
 * @mixin \Eloquent
 */
class PalletStoredItem extends Model
{
    protected $guarded = [];

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class);
    }

    public function storedItem(): BelongsTo
    {
        return $this->belongsTo(StoredItem::class);
    }
}
