<?php
/*
 * author Arya Permana - Kirin
 * created on 17-12-2024-13h-29m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOrderingIntervals extends Model
{
    protected $table = 'shop_ordering_intervals';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
