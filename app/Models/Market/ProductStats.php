<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:41:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Market\ProductStats
 *
 * @property int $id
 * @property int $product_id
 * @property int $number_outers
 * @property int $number_outers_available
 * @property int $number_outers_state_in_process
 * @property int $number_outers_state_active
 * @property int $number_outers_state_discontinuing
 * @property int $number_outers_state_discontinued
 * @property int $number_historic_outers
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Market\Product $product
 * @method static Builder|ProductStats newModelQuery()
 * @method static Builder|ProductStats newQuery()
 * @method static Builder|ProductStats query()
 * @mixin Eloquent
 */
class ProductStats extends Model
{
    protected $table = 'product_stats';

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
