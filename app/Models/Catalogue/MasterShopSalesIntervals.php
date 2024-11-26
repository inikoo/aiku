<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-09h-42m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Catalogue\MasterShopSalesIntervals
 *
 * @property int $id
 * @property int $master_shop_id
 * @property string $master_shop_amount_all
 * @property string $master_shop_amount_1y
 * @property string $master_shop_amount_1q
 * @property string $master_shop_amount_1m
 * @property string $master_shop_amount_1w
 * @property string $master_shop_amount_3d
 * @property string $master_shop_amount_1d
 * @property string $master_shop_amount_ytd
 * @property string $master_shop_amount_qtd
 * @property string $master_shop_amount_mtd
 * @property string $master_shop_amount_wtd
 * @property string $master_shop_amount_lm
 * @property string $master_shop_amount_lw
 * @property string $master_shop_amount_all_ly
 * @property string $master_shop_amount_1y_ly
 * @property string $master_shop_amount_1q_ly
 * @property string $master_shop_amount_1m_ly
 * @property string $master_shop_amount_1w_ly
 * @property string $master_shop_amount_3d_ly
 * @property string $master_shop_amount_1d_ly
 * @property string $master_shop_amount_ytd_ly
 * @property string $master_shop_amount_qtd_ly
 * @property string $master_shop_amount_mtd_ly
 * @property string $master_shop_amount_wtd_ly
 * @property string $master_shop_amount_lm_ly
 * @property string $master_shop_amount_lw_ly
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
 * @property string $group_amount_3d
 * @property string $group_amount_1d
 * @property string $group_amount_ytd
 * @property string $group_amount_qtd
 * @property string $group_amount_mtd
 * @property string $group_amount_wtd
 * @property string $group_amount_lm
 * @property string $group_amount_lw
 * @property string $group_amount_all_ly
 * @property string $group_amount_1y_ly
 * @property string $group_amount_1q_ly
 * @property string $group_amount_1m_ly
 * @property string $group_amount_1w_ly
 * @property string $group_amount_3d_ly
 * @property string $group_amount_1d_ly
 * @property string $group_amount_ytd_ly
 * @property string $group_amount_qtd_ly
 * @property string $group_amount_mtd_ly
 * @property string $group_amount_wtd_ly
 * @property string $group_amount_lm_ly
 * @property string $group_amount_lw_ly
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
 * @property-read \App\Models\Catalogue\MasterShop $masterShop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopSalesIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopSalesIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopSalesIntervals query()
 * @mixin \Eloquent
 */
class MasterShopSalesIntervals extends Model
{
    protected $table = 'master_shop_sales_intervals';

    protected $guarded = [];

    public function masterShop(): BelongsTo
    {
        return $this->belongsTo(MasterShop::class);
    }
}
