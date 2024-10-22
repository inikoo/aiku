<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-09h-46m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MasterProductSalesIntervals
 *
 * @property int $id
 * @property int $master_product_id
 * @property string $master_shop_amount_all
 * @property string $master_shop_amount_1y
 * @property string $master_shop_amount_1q
 * @property string $master_shop_amount_1m
 * @property string $master_shop_amount_1w
 * @property string $master_shop_amount_ytd
 * @property string $master_shop_amount_qtd
 * @property string $master_shop_amount_mtd
 * @property string $master_shop_amount_wtd
 * @property string $master_shop_amount_lm
 * @property string $master_shop_amount_lw
 * @property string $master_shop_amount_yda
 * @property string $master_shop_amount_tdy
 * @property string $master_shop_amount_all_ly
 * @property string $master_shop_amount_1y_ly
 * @property string $master_shop_amount_1q_ly
 * @property string $master_shop_amount_1m_ly
 * @property string $master_shop_amount_1w_ly
 * @property string $master_shop_amount_ytd_ly
 * @property string $master_shop_amount_qtd_ly
 * @property string $master_shop_amount_mtd_ly
 * @property string $master_shop_amount_wtd_ly
 * @property string $master_shop_amount_lm_ly
 * @property string $master_shop_amount_lw_ly
 * @property string $master_shop_amount_yda_ly
 * @property string $master_shop_amount_tdy_ly
 * @property string $master_shop_amount_py1
 * @property string $master_shop_amount_py2
 * @property string $master_shop_amount_py3
 * @property string $master_shop_amount_py4
 * @property string $master_shop_amount_py5
 * @property string $master_shop_amount_pq1
 * @property string $master_shop_amount_pq2
 * @property string $master_shop_amount_pq3
 * @property string $master_shop_amount_pq4
 * @property string $master_shop_amount_pq5
 * @property string $group_amount_all
 * @property string $group_amount_1y
 * @property string $group_amount_1q
 * @property string $group_amount_1m
 * @property string $group_amount_1w
 * @property string $group_amount_ytd
 * @property string $group_amount_qtd
 * @property string $group_amount_mtd
 * @property string $group_amount_wtd
 * @property string $group_amount_lm
 * @property string $group_amount_lw
 * @property string $group_amount_yda
 * @property string $group_amount_tdy
 * @property string $group_amount_all_ly
 * @property string $group_amount_1y_ly
 * @property string $group_amount_1q_ly
 * @property string $group_amount_1m_ly
 * @property string $group_amount_1w_ly
 * @property string $group_amount_ytd_ly
 * @property string $group_amount_qtd_ly
 * @property string $group_amount_mtd_ly
 * @property string $group_amount_wtd_ly
 * @property string $group_amount_lm_ly
 * @property string $group_amount_lw_ly
 * @property string $group_amount_yda_ly
 * @property string $group_amount_tdy_ly
 * @property string $group_amount_py1
 * @property string $group_amount_py2
 * @property string $group_amount_py3
 * @property string $group_amount_py4
 * @property string $group_amount_py5
 * @property string $group_amount_pq1
 * @property string $group_amount_pq2
 * @property string $group_amount_pq3
 * @property string $group_amount_pq4
 * @property string $group_amount_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\MasterProduct $masterProduct
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductSalesIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductSalesIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductSalesIntervals query()
 * @mixin \Eloquent
 */
class MasterProductSalesIntervals extends Model
{
    protected $guarded = [];

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }
}
