<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 21:30:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDiscountsStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDiscountsStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDiscountsStats query()
 * @mixin \Eloquent
 */
class ShopDiscountsStats extends Model
{
    protected $table = 'shop_discounts_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
