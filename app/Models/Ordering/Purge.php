<?php
/*
 * author Arya Permana - Kirin
 * created on 01-11-2024-10h-47m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Ordering;

use App\Enums\Ordering\Adjustment\AdjustmentTypeEnum;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Helpers\Currency;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purge extends Model
{
    use InShop;
    protected $guarded = [];

    protected $casts = [
        'type'       => PurgeTypeEnum::class,
        'state'     => PurgeStateEnum::class,
        'date'         => 'datetime',
    ];

    public function purgedOrders(): HasMany
    {
        return $this->hasMany(PurgedOrder::class);
    }

}
