<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 14:58:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Sales\Order $order
 * @method static Builder|OrderStats newModelQuery()
 * @method static Builder|OrderStats newQuery()
 * @method static Builder|OrderStats query()
 * @mixin Eloquent
 */
class OrderStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'order_stats';

    protected $guarded = [];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
