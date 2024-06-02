<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 12:57:50 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $product_variant_id
 * @property int $number_historic_product_variants
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\ProductVariant $productVariant
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantStats query()
 * @mixin \Eloquent
 */
class ProductVariantStats extends Model
{
    protected $table = 'product_variant_stats';

    protected $guarded = [];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
