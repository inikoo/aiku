<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Nov 2024 10:50:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\Catalogue\ProductCategory|null $productCategory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategoryOrderingIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategoryOrderingIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategoryOrderingIntervals query()
 * @mixin \Eloquent
 */
class ProductCategoryOrderingIntervals extends Model
{
    protected $guarded = [];

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
