<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-09h-11m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerPollOption extends Model
{
    protected $casts = [
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(CustomerPollOptionStat::class);
    }

    public function customerPoll(): BelongsTo
    {
        return $this->belongsTo(CustomerPoll::class);
    }
}
