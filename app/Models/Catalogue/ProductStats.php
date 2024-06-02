<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:15:49 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $product_id
 * @property int $number_historic_assets
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Product|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats query()
 * @mixin \Eloquent
 */
class ProductStats extends Model
{
    protected $table = 'product_stats';

    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
