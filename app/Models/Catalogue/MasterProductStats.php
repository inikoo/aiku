<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-09h-58m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $product_id
 * @property int $number_product_variants
 * @property int $number_historic_assets
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\MasterProduct $masterProduct
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductStats query()
 * @mixin \Eloquent
 */
class MasterProductStats extends Model
{
    protected $table = 'master_product_stats';

    protected $guarded = [];

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }
}
