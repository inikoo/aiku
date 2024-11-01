<?php
/*
 * author Arya Permana - Kirin
 * created on 01-11-2024-11h-30m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Ordering;

use App\Enums\Ordering\Adjustment\AdjustmentTypeEnum;
use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Helpers\Currency;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurgedOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status'       => PurgedOrderStatusEnum::class,
    ];

    public function purge(): BelongsTo
    {
        return $this->belongsTo(Purge::class, 'purge_id');
    }



}
