<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-09h-07m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $customer_poll_id
 * @property int $number_customers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPollStat query()
 * @mixin \Eloquent
 */
class CustomerPollStat extends Model
{
    protected $casts = [
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

}
