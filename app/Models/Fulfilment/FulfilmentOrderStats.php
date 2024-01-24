<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 04 Dec 2022 18:27:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Fulfilment\Fulfilment\FulfilmentOrderStats
 *
 * @property int $id
 * @property int $fulfilment_order_id
 * @property int $number_items_at_creation
 * @property int $number_cancelled_items
 * @property int $number_add_up_items
 * @property int $number_cut_off_items
 * @property int $number_items_fulfilled
 * @property int $number_items current number of items
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\FulfilmentOrder $fulfilmentOrder
 * @method static Builder|FulfilmentOrderStats newModelQuery()
 * @method static Builder|FulfilmentOrderStats newQuery()
 * @method static Builder|FulfilmentOrderStats query()
 * @mixin Eloquent
 */
class FulfilmentOrderStats extends Model
{
    protected $table   = 'fulfilment_order_stats';
    protected $guarded = [];

    public function fulfilmentOrder(): BelongsTo
    {
        return $this->belongsTo(FulfilmentOrder::class);
    }
}
