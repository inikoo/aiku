<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 22:45:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Models\Goods\StockFamily;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $stock_family_id
 * @property string $group_amount_revenue_all
 * @property string $group_amount_revenue_1y
 * @property string $group_amount_revenue_1q
 * @property string $group_amount_revenue_1m
 * @property string $group_amount_revenue_1w
 * @property string $group_amount_revenue_3d
 * @property string $group_amount_revenue_1d
 * @property string $group_amount_revenue_ytd
 * @property string $group_amount_revenue_qtd
 * @property string $group_amount_revenue_mtd
 * @property string $group_amount_revenue_wtd
 * @property string $group_amount_revenue_tdy
 * @property string $group_amount_revenue_lm
 * @property string $group_amount_revenue_lw
 * @property string $group_amount_revenue_ld
 * @property string $group_amount_revenue_1y_ly
 * @property string $group_amount_revenue_1q_ly
 * @property string $group_amount_revenue_1m_ly
 * @property string $group_amount_revenue_1w_ly
 * @property string $group_amount_revenue_3d_ly
 * @property string $group_amount_revenue_1d_ly
 * @property string $group_amount_revenue_ytd_ly
 * @property string $group_amount_revenue_qtd_ly
 * @property string $group_amount_revenue_mtd_ly
 * @property string $group_amount_revenue_wtd_ly
 * @property string $group_amount_revenue_tdy_ly
 * @property string $group_amount_revenue_lm_ly
 * @property string $group_amount_revenue_lw_ly
 * @property string $group_amount_revenue_ld_ly
 * @property string $group_amount_revenue_py1
 * @property string $group_amount_revenue_py2
 * @property string $group_amount_revenue_py3
 * @property string $group_amount_revenue_py4
 * @property string $group_amount_revenue_py5
 * @property string $group_amount_revenue_pq1
 * @property string $group_amount_revenue_pq2
 * @property string $group_amount_revenue_pq3
 * @property string $group_amount_revenue_pq4
 * @property string $group_amount_revenue_pq5
 * @property string $group_amount_profit_all
 * @property string $group_amount_profit_1y
 * @property string $group_amount_profit_1q
 * @property string $group_amount_profit_1m
 * @property string $group_amount_profit_1w
 * @property string $group_amount_profit_3d
 * @property string $group_amount_profit_1d
 * @property string $group_amount_profit_ytd
 * @property string $group_amount_profit_qtd
 * @property string $group_amount_profit_mtd
 * @property string $group_amount_profit_wtd
 * @property string $group_amount_profit_tdy
 * @property string $group_amount_profit_lm
 * @property string $group_amount_profit_lw
 * @property string $group_amount_profit_ld
 * @property string $group_amount_profit_1y_ly
 * @property string $group_amount_profit_1q_ly
 * @property string $group_amount_profit_1m_ly
 * @property string $group_amount_profit_1w_ly
 * @property string $group_amount_profit_3d_ly
 * @property string $group_amount_profit_1d_ly
 * @property string $group_amount_profit_ytd_ly
 * @property string $group_amount_profit_qtd_ly
 * @property string $group_amount_profit_mtd_ly
 * @property string $group_amount_profit_wtd_ly
 * @property string $group_amount_profit_tdy_ly
 * @property string $group_amount_profit_lm_ly
 * @property string $group_amount_profit_lw_ly
 * @property string $group_amount_profit_ld_ly
 * @property string $group_amount_profit_py1
 * @property string $group_amount_profit_py2
 * @property string $group_amount_profit_py3
 * @property string $group_amount_profit_py4
 * @property string $group_amount_profit_py5
 * @property string $group_amount_profit_pq1
 * @property string $group_amount_profit_pq2
 * @property string $group_amount_profit_pq3
 * @property string $group_amount_profit_pq4
 * @property string $group_amount_profit_pq5
 * @property int $dispatched_all
 * @property int $dispatched_1y
 * @property int $dispatched_1q
 * @property int $dispatched_1m
 * @property int $dispatched_1w
 * @property int $dispatched_3d
 * @property int $dispatched_1d
 * @property int $dispatched_ytd
 * @property int $dispatched_qtd
 * @property int $dispatched_mtd
 * @property int $dispatched_wtd
 * @property int $dispatched_tdy
 * @property int $dispatched_lm
 * @property int $dispatched_lw
 * @property int $dispatched_ld
 * @property int $dispatched_1y_ly
 * @property int $dispatched_1q_ly
 * @property int $dispatched_1m_ly
 * @property int $dispatched_1w_ly
 * @property int $dispatched_3d_ly
 * @property int $dispatched_1d_ly
 * @property int $dispatched_ytd_ly
 * @property int $dispatched_qtd_ly
 * @property int $dispatched_mtd_ly
 * @property int $dispatched_wtd_ly
 * @property int $dispatched_tdy_ly
 * @property int $dispatched_lm_ly
 * @property int $dispatched_lw_ly
 * @property int $dispatched_ld_ly
 * @property int $dispatched_py1
 * @property int $dispatched_py2
 * @property int $dispatched_py3
 * @property int $dispatched_py4
 * @property int $dispatched_py5
 * @property int $dispatched_pq1
 * @property int $dispatched_pq2
 * @property int $dispatched_pq3
 * @property int $dispatched_pq4
 * @property int $dispatched_pq5
 * @property int $org_stock_movements_all
 * @property int $org_stock_movements_1y
 * @property int $org_stock_movements_1q
 * @property int $org_stock_movements_1m
 * @property int $org_stock_movements_1w
 * @property int $org_stock_movements_3d
 * @property int $org_stock_movements_1d
 * @property int $org_stock_movements_ytd
 * @property int $org_stock_movements_qtd
 * @property int $org_stock_movements_mtd
 * @property int $org_stock_movements_wtd
 * @property int $org_stock_movements_tdy
 * @property int $org_stock_movements_lm
 * @property int $org_stock_movements_lw
 * @property int $org_stock_movements_ld
 * @property int $org_stock_movements_1y_ly
 * @property int $org_stock_movements_1q_ly
 * @property int $org_stock_movements_1m_ly
 * @property int $org_stock_movements_1w_ly
 * @property int $org_stock_movements_3d_ly
 * @property int $org_stock_movements_1d_ly
 * @property int $org_stock_movements_ytd_ly
 * @property int $org_stock_movements_qtd_ly
 * @property int $org_stock_movements_mtd_ly
 * @property int $org_stock_movements_wtd_ly
 * @property int $org_stock_movements_tdy_ly
 * @property int $org_stock_movements_lm_ly
 * @property int $org_stock_movements_lw_ly
 * @property int $org_stock_movements_ld_ly
 * @property int $org_stock_movements_py1
 * @property int $org_stock_movements_py2
 * @property int $org_stock_movements_py3
 * @property int $org_stock_movements_py4
 * @property int $org_stock_movements_py5
 * @property int $org_stock_movements_pq1
 * @property int $org_stock_movements_pq2
 * @property int $org_stock_movements_pq3
 * @property int $org_stock_movements_pq4
 * @property int $org_stock_movements_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read StockFamily $stockFamily
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockFamilyIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockFamilyIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockFamilyIntervals query()
 * @mixin \Eloquent
 */
class StockFamilyIntervals extends Model
{
    protected $table = 'stock_family_intervals';

    protected $guarded = [];


    public function stockFamily(): BelongsTo
    {
        return $this->belongsTo(StockFamily::class);
    }
}
