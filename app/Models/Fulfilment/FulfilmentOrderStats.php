<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 04 Dec 2022 18:27:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fulfilment\FulfilmentOrderStats
 *
 * @property int $id
 * @property int $order_id
 * @property int $number_items_at_creation
 * @property int $number_cancelled_items
 * @property int $number_add_up_items
 * @property int $number_cut_off_items
 * @property int $items_fulfilled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\FulfilmentOrder $fulfilmentOrder
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereItemsFulfilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereNumberAddUpItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereNumberCancelledItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereNumberCutOffItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereNumberItemsAtCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentOrderStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FulfilmentOrderStats extends Model
{
    protected $table = 'fulfilment_order_stats';

    protected $guarded = [];


    public function fulfilmentOrder(): BelongsTo
    {
        return $this->belongsTo(FulfilmentOrder::class);
    }

}
