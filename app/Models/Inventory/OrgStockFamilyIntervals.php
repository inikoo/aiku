<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 22:44:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $org_stock_family_id
 * @property string $revenue_org_currency_all
 * @property string $revenue_org_currency_1y
 * @property string $revenue_org_currency_1q
 * @property string $revenue_org_currency_1m
 * @property string $revenue_org_currency_1w
 * @property string $revenue_org_currency_3d
 * @property string $revenue_org_currency_1d
 * @property string $revenue_org_currency_ytd
 * @property string $revenue_org_currency_qtd
 * @property string $revenue_org_currency_mtd
 * @property string $revenue_org_currency_wtd
 * @property string $revenue_org_currency_tdy
 * @property string $revenue_org_currency_lm
 * @property string $revenue_org_currency_lw
 * @property string $revenue_org_currency_ld
 * @property string $revenue_org_currency_1y_ly
 * @property string $revenue_org_currency_1q_ly
 * @property string $revenue_org_currency_1m_ly
 * @property string $revenue_org_currency_1w_ly
 * @property string $revenue_org_currency_3d_ly
 * @property string $revenue_org_currency_1d_ly
 * @property string $revenue_org_currency_ytd_ly
 * @property string $revenue_org_currency_qtd_ly
 * @property string $revenue_org_currency_mtd_ly
 * @property string $revenue_org_currency_wtd_ly
 * @property string $revenue_org_currency_tdy_ly
 * @property string $revenue_org_currency_lm_ly
 * @property string $revenue_org_currency_lw_ly
 * @property string $revenue_org_currency_ld_ly
 * @property string $revenue_org_currency_py1
 * @property string $revenue_org_currency_py2
 * @property string $revenue_org_currency_py3
 * @property string $revenue_org_currency_py4
 * @property string $revenue_org_currency_py5
 * @property string $revenue_org_currency_pq1
 * @property string $revenue_org_currency_pq2
 * @property string $revenue_org_currency_pq3
 * @property string $revenue_org_currency_pq4
 * @property string $revenue_org_currency_pq5
 * @property string $revenue_grp_currency_all
 * @property string $revenue_grp_currency_1y
 * @property string $revenue_grp_currency_1q
 * @property string $revenue_grp_currency_1m
 * @property string $revenue_grp_currency_1w
 * @property string $revenue_grp_currency_3d
 * @property string $revenue_grp_currency_1d
 * @property string $revenue_grp_currency_ytd
 * @property string $revenue_grp_currency_qtd
 * @property string $revenue_grp_currency_mtd
 * @property string $revenue_grp_currency_wtd
 * @property string $revenue_grp_currency_tdy
 * @property string $revenue_grp_currency_lm
 * @property string $revenue_grp_currency_lw
 * @property string $revenue_grp_currency_ld
 * @property string $revenue_grp_currency_1y_ly
 * @property string $revenue_grp_currency_1q_ly
 * @property string $revenue_grp_currency_1m_ly
 * @property string $revenue_grp_currency_1w_ly
 * @property string $revenue_grp_currency_3d_ly
 * @property string $revenue_grp_currency_1d_ly
 * @property string $revenue_grp_currency_ytd_ly
 * @property string $revenue_grp_currency_qtd_ly
 * @property string $revenue_grp_currency_mtd_ly
 * @property string $revenue_grp_currency_wtd_ly
 * @property string $revenue_grp_currency_tdy_ly
 * @property string $revenue_grp_currency_lm_ly
 * @property string $revenue_grp_currency_lw_ly
 * @property string $revenue_grp_currency_ld_ly
 * @property string $revenue_grp_currency_py1
 * @property string $revenue_grp_currency_py2
 * @property string $revenue_grp_currency_py3
 * @property string $revenue_grp_currency_py4
 * @property string $revenue_grp_currency_py5
 * @property string $revenue_grp_currency_pq1
 * @property string $revenue_grp_currency_pq2
 * @property string $revenue_grp_currency_pq3
 * @property string $revenue_grp_currency_pq4
 * @property string $revenue_grp_currency_pq5
 * @property string $profit_org_currency_all
 * @property string $profit_org_currency_1y
 * @property string $profit_org_currency_1q
 * @property string $profit_org_currency_1m
 * @property string $profit_org_currency_1w
 * @property string $profit_org_currency_3d
 * @property string $profit_org_currency_1d
 * @property string $profit_org_currency_ytd
 * @property string $profit_org_currency_qtd
 * @property string $profit_org_currency_mtd
 * @property string $profit_org_currency_wtd
 * @property string $profit_org_currency_tdy
 * @property string $profit_org_currency_lm
 * @property string $profit_org_currency_lw
 * @property string $profit_org_currency_ld
 * @property string $profit_org_currency_1y_ly
 * @property string $profit_org_currency_1q_ly
 * @property string $profit_org_currency_1m_ly
 * @property string $profit_org_currency_1w_ly
 * @property string $profit_org_currency_3d_ly
 * @property string $profit_org_currency_1d_ly
 * @property string $profit_org_currency_ytd_ly
 * @property string $profit_org_currency_qtd_ly
 * @property string $profit_org_currency_mtd_ly
 * @property string $profit_org_currency_wtd_ly
 * @property string $profit_org_currency_tdy_ly
 * @property string $profit_org_currency_lm_ly
 * @property string $profit_org_currency_lw_ly
 * @property string $profit_org_currency_ld_ly
 * @property string $profit_org_currency_py1
 * @property string $profit_org_currency_py2
 * @property string $profit_org_currency_py3
 * @property string $profit_org_currency_py4
 * @property string $profit_org_currency_py5
 * @property string $profit_org_currency_pq1
 * @property string $profit_org_currency_pq2
 * @property string $profit_org_currency_pq3
 * @property string $profit_org_currency_pq4
 * @property string $profit_org_currency_pq5
 * @property string $profit_grp_currency_all
 * @property string $profit_grp_currency_1y
 * @property string $profit_grp_currency_1q
 * @property string $profit_grp_currency_1m
 * @property string $profit_grp_currency_1w
 * @property string $profit_grp_currency_3d
 * @property string $profit_grp_currency_1d
 * @property string $profit_grp_currency_ytd
 * @property string $profit_grp_currency_qtd
 * @property string $profit_grp_currency_mtd
 * @property string $profit_grp_currency_wtd
 * @property string $profit_grp_currency_tdy
 * @property string $profit_grp_currency_lm
 * @property string $profit_grp_currency_lw
 * @property string $profit_grp_currency_ld
 * @property string $profit_grp_currency_1y_ly
 * @property string $profit_grp_currency_1q_ly
 * @property string $profit_grp_currency_1m_ly
 * @property string $profit_grp_currency_1w_ly
 * @property string $profit_grp_currency_3d_ly
 * @property string $profit_grp_currency_1d_ly
 * @property string $profit_grp_currency_ytd_ly
 * @property string $profit_grp_currency_qtd_ly
 * @property string $profit_grp_currency_mtd_ly
 * @property string $profit_grp_currency_wtd_ly
 * @property string $profit_grp_currency_tdy_ly
 * @property string $profit_grp_currency_lm_ly
 * @property string $profit_grp_currency_lw_ly
 * @property string $profit_grp_currency_ld_ly
 * @property string $profit_grp_currency_py1
 * @property string $profit_grp_currency_py2
 * @property string $profit_grp_currency_py3
 * @property string $profit_grp_currency_py4
 * @property string $profit_grp_currency_py5
 * @property string $profit_grp_currency_pq1
 * @property string $profit_grp_currency_pq2
 * @property string $profit_grp_currency_pq3
 * @property string $profit_grp_currency_pq4
 * @property string $profit_grp_currency_pq5
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
 * @property-read \App\Models\Inventory\OrgStockFamily $orgStockFamily
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyIntervals query()
 * @mixin \Eloquent
 */
class OrgStockFamilyIntervals extends Model
{
    protected $table = 'org_stock_family_intervals';

    protected $guarded = [];


    public function orgStockFamily(): BelongsTo
    {
        return $this->belongsTo(OrgStockFamily::class);
    }
}
