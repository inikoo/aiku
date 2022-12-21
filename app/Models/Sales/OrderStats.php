<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 14:58:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Sales\OrderStats
 *
 * @property int $id
 * @property int $order_id
 * @property int $number_items_at_creation
 * @property int $number_cancelled_items
 * @property int $number_add_up_items
 * @property int $number_cut_off_items
 * @property int $number_items_dispatched
 * @property int $number_items current number of items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Sales\Order $order
 * @method static Builder|OrderStats newModelQuery()
 * @method static Builder|OrderStats newQuery()
 * @method static Builder|OrderStats query()
 * @method static Builder|OrderStats whereCreatedAt($value)
 * @method static Builder|OrderStats whereId($value)
 * @method static Builder|OrderStats whereNumberAddUpItems($value)
 * @method static Builder|OrderStats whereNumberCancelledItems($value)
 * @method static Builder|OrderStats whereNumberCutOffItems($value)
 * @method static Builder|OrderStats whereNumberItems($value)
 * @method static Builder|OrderStats whereNumberItemsAtCreation($value)
 * @method static Builder|OrderStats whereNumberItemsDispatched($value)
 * @method static Builder|OrderStats whereOrderId($value)
 * @method static Builder|OrderStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderStats extends Model
{
    protected $table = 'order_stats';

    protected $guarded = [];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
