<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:15:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Catalogue\MasterShopSalesIntervals
 *
 * @property int $id
 * @property int $master_shop_id
 * @property string $grp_amount_all
 * @property string $grp_amount_1y
 * @property string $grp_amount_1q
 * @property string $grp_amount_1m
 * @property string $grp_amount_1w
 * @property string $grp_amount_3d
 * @property string $grp_amount_1d
 * @property string $grp_amount_ytd
 * @property string $grp_amount_qtd
 * @property string $grp_amount_mtd
 * @property string $grp_amount_wtd
 * @property string $grp_amount_tdy
 * @property string $grp_amount_lm
 * @property string $grp_amount_lw
 * @property string $grp_amount_ld
 * @property string $grp_amount_1y_ly
 * @property string $grp_amount_1q_ly
 * @property string $grp_amount_1m_ly
 * @property string $grp_amount_1w_ly
 * @property string $grp_amount_3d_ly
 * @property string $grp_amount_1d_ly
 * @property string $grp_amount_ytd_ly
 * @property string $grp_amount_qtd_ly
 * @property string $grp_amount_mtd_ly
 * @property string $grp_amount_wtd_ly
 * @property string $grp_amount_tdy_ly
 * @property string $grp_amount_lm_ly
 * @property string $grp_amount_lw_ly
 * @property string $grp_amount_ld_ly
 * @property string $grp_amount_py1
 * @property string $grp_amount_py2
 * @property string $grp_amount_py3
 * @property string $grp_amount_py4
 * @property string $grp_amount_py5
 * @property string $grp_amount_pq1
 * @property string $grp_amount_pq2
 * @property string $grp_amount_pq3
 * @property string $grp_amount_pq4
 * @property string $grp_amount_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Goods\MasterShop $masterShop
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
