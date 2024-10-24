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

/**
 *
 *
 * @property int $id
 * @property int $customer_poll_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\CustomerPoll $customerPoll
 * @property-read \App\Models\Web\CustomerPollOptionStat|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollOption query()
 * @mixin \Eloquent
 */
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
