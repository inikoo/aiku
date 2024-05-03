<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 20:29:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property string $in_baskets_all
 * @property string $in_baskets_1y
 * @property string $in_baskets_1q
 * @property string $in_baskets_1m
 * @property string $in_baskets_1w
 * @property string $in_baskets_ytd
 * @property string $in_baskets_qtd
 * @property string $in_baskets_mtd
 * @property string $in_baskets_wtd
 * @property string $in_baskets_lm
 * @property string $in_baskets_lw
 * @property string $in_baskets_yda
 * @property string $in_baskets_tdy
 * @property string $in_baskets_all_ly
 * @property string $in_baskets_1y_ly
 * @property string $in_baskets_1q_ly
 * @property string $in_baskets_1m_ly
 * @property string $in_baskets_1w_ly
 * @property string $in_baskets_ytd_ly
 * @property string $in_baskets_qtd_ly
 * @property string $in_baskets_mtd_ly
 * @property string $in_baskets_wtd_ly
 * @property string $in_baskets_lm_ly
 * @property string $in_baskets_lw_ly
 * @property string $in_baskets_yda_ly
 * @property string $in_baskets_tdy_ly
 * @property string $in_baskets_py1
 * @property string $in_baskets_py2
 * @property string $in_baskets_py3
 * @property string $in_baskets_py4
 * @property string $in_baskets_py5
 * @property string $in_baskets_pq1
 * @property string $in_baskets_pq2
 * @property string $in_baskets_pq3
 * @property string $in_baskets_pq4
 * @property string $in_baskets_pq5
 * @property string $in_process_all
 * @property string $in_process_1y
 * @property string $in_process_1q
 * @property string $in_process_1m
 * @property string $in_process_1w
 * @property string $in_process_ytd
 * @property string $in_process_qtd
 * @property string $in_process_mtd
 * @property string $in_process_wtd
 * @property string $in_process_lm
 * @property string $in_process_lw
 * @property string $in_process_yda
 * @property string $in_process_tdy
 * @property string $in_process_all_ly
 * @property string $in_process_1y_ly
 * @property string $in_process_1q_ly
 * @property string $in_process_1m_ly
 * @property string $in_process_1w_ly
 * @property string $in_process_ytd_ly
 * @property string $in_process_qtd_ly
 * @property string $in_process_mtd_ly
 * @property string $in_process_wtd_ly
 * @property string $in_process_lm_ly
 * @property string $in_process_lw_ly
 * @property string $in_process_yda_ly
 * @property string $in_process_tdy_ly
 * @property string $in_process_py1
 * @property string $in_process_py2
 * @property string $in_process_py3
 * @property string $in_process_py4
 * @property string $in_process_py5
 * @property string $in_process_pq1
 * @property string $in_process_pq2
 * @property string $in_process_pq3
 * @property string $in_process_pq4
 * @property string $in_process_pq5
 * @property string $in_process_paid_all
 * @property string $in_process_paid_1y
 * @property string $in_process_paid_1q
 * @property string $in_process_paid_1m
 * @property string $in_process_paid_1w
 * @property string $in_process_paid_ytd
 * @property string $in_process_paid_qtd
 * @property string $in_process_paid_mtd
 * @property string $in_process_paid_wtd
 * @property string $in_process_paid_lm
 * @property string $in_process_paid_lw
 * @property string $in_process_paid_yda
 * @property string $in_process_paid_tdy
 * @property string $in_process_paid_all_ly
 * @property string $in_process_paid_1y_ly
 * @property string $in_process_paid_1q_ly
 * @property string $in_process_paid_1m_ly
 * @property string $in_process_paid_1w_ly
 * @property string $in_process_paid_ytd_ly
 * @property string $in_process_paid_qtd_ly
 * @property string $in_process_paid_mtd_ly
 * @property string $in_process_paid_wtd_ly
 * @property string $in_process_paid_lm_ly
 * @property string $in_process_paid_lw_ly
 * @property string $in_process_paid_yda_ly
 * @property string $in_process_paid_tdy_ly
 * @property string $in_process_paid_py1
 * @property string $in_process_paid_py2
 * @property string $in_process_paid_py3
 * @property string $in_process_paid_py4
 * @property string $in_process_paid_py5
 * @property string $in_process_paid_pq1
 * @property string $in_process_paid_pq2
 * @property string $in_process_paid_pq3
 * @property string $in_process_paid_pq4
 * @property string $in_process_paid_pq5
 * @property string $in_warehouse_all
 * @property string $in_warehouse_1y
 * @property string $in_warehouse_1q
 * @property string $in_warehouse_1m
 * @property string $in_warehouse_1w
 * @property string $in_warehouse_ytd
 * @property string $in_warehouse_qtd
 * @property string $in_warehouse_mtd
 * @property string $in_warehouse_wtd
 * @property string $in_warehouse_lm
 * @property string $in_warehouse_lw
 * @property string $in_warehouse_yda
 * @property string $in_warehouse_tdy
 * @property string $in_warehouse_all_ly
 * @property string $in_warehouse_1y_ly
 * @property string $in_warehouse_1q_ly
 * @property string $in_warehouse_1m_ly
 * @property string $in_warehouse_1w_ly
 * @property string $in_warehouse_ytd_ly
 * @property string $in_warehouse_qtd_ly
 * @property string $in_warehouse_mtd_ly
 * @property string $in_warehouse_wtd_ly
 * @property string $in_warehouse_lm_ly
 * @property string $in_warehouse_lw_ly
 * @property string $in_warehouse_yda_ly
 * @property string $in_warehouse_tdy_ly
 * @property string $in_warehouse_py1
 * @property string $in_warehouse_py2
 * @property string $in_warehouse_py3
 * @property string $in_warehouse_py4
 * @property string $in_warehouse_py5
 * @property string $in_warehouse_pq1
 * @property string $in_warehouse_pq2
 * @property string $in_warehouse_pq3
 * @property string $in_warehouse_pq4
 * @property string $in_warehouse_pq5
 * @property string $packed_all
 * @property string $packed_1y
 * @property string $packed_1q
 * @property string $packed_1m
 * @property string $packed_1w
 * @property string $packed_ytd
 * @property string $packed_qtd
 * @property string $packed_mtd
 * @property string $packed_wtd
 * @property string $packed_lm
 * @property string $packed_lw
 * @property string $packed_yda
 * @property string $packed_tdy
 * @property string $packed_all_ly
 * @property string $packed_1y_ly
 * @property string $packed_1q_ly
 * @property string $packed_1m_ly
 * @property string $packed_1w_ly
 * @property string $packed_ytd_ly
 * @property string $packed_qtd_ly
 * @property string $packed_mtd_ly
 * @property string $packed_wtd_ly
 * @property string $packed_lm_ly
 * @property string $packed_lw_ly
 * @property string $packed_yda_ly
 * @property string $packed_tdy_ly
 * @property string $packed_py1
 * @property string $packed_py2
 * @property string $packed_py3
 * @property string $packed_py4
 * @property string $packed_py5
 * @property string $packed_pq1
 * @property string $packed_pq2
 * @property string $packed_pq3
 * @property string $packed_pq4
 * @property string $packed_pq5
 * @property string $in_dispatch_area_all
 * @property string $in_dispatch_area_1y
 * @property string $in_dispatch_area_1q
 * @property string $in_dispatch_area_1m
 * @property string $in_dispatch_area_1w
 * @property string $in_dispatch_area_ytd
 * @property string $in_dispatch_area_qtd
 * @property string $in_dispatch_area_mtd
 * @property string $in_dispatch_area_wtd
 * @property string $in_dispatch_area_lm
 * @property string $in_dispatch_area_lw
 * @property string $in_dispatch_area_yda
 * @property string $in_dispatch_area_tdy
 * @property string $in_dispatch_area_all_ly
 * @property string $in_dispatch_area_1y_ly
 * @property string $in_dispatch_area_1q_ly
 * @property string $in_dispatch_area_1m_ly
 * @property string $in_dispatch_area_1w_ly
 * @property string $in_dispatch_area_ytd_ly
 * @property string $in_dispatch_area_qtd_ly
 * @property string $in_dispatch_area_mtd_ly
 * @property string $in_dispatch_area_wtd_ly
 * @property string $in_dispatch_area_lm_ly
 * @property string $in_dispatch_area_lw_ly
 * @property string $in_dispatch_area_yda_ly
 * @property string $in_dispatch_area_tdy_ly
 * @property string $in_dispatch_area_py1
 * @property string $in_dispatch_area_py2
 * @property string $in_dispatch_area_py3
 * @property string $in_dispatch_area_py4
 * @property string $in_dispatch_area_py5
 * @property string $in_dispatch_area_pq1
 * @property string $in_dispatch_area_pq2
 * @property string $in_dispatch_area_pq3
 * @property string $in_dispatch_area_pq4
 * @property string $in_dispatch_area_pq5
 * @property string $delivery_notes_all
 * @property string $delivery_notes_1y
 * @property string $delivery_notes_1q
 * @property string $delivery_notes_1m
 * @property string $delivery_notes_1w
 * @property string $delivery_notes_ytd
 * @property string $delivery_notes_qtd
 * @property string $delivery_notes_mtd
 * @property string $delivery_notes_wtd
 * @property string $delivery_notes_lm
 * @property string $delivery_notes_lw
 * @property string $delivery_notes_yda
 * @property string $delivery_notes_tdy
 * @property string $delivery_notes_all_ly
 * @property string $delivery_notes_1y_ly
 * @property string $delivery_notes_1q_ly
 * @property string $delivery_notes_1m_ly
 * @property string $delivery_notes_1w_ly
 * @property string $delivery_notes_ytd_ly
 * @property string $delivery_notes_qtd_ly
 * @property string $delivery_notes_mtd_ly
 * @property string $delivery_notes_wtd_ly
 * @property string $delivery_notes_lm_ly
 * @property string $delivery_notes_lw_ly
 * @property string $delivery_notes_yda_ly
 * @property string $delivery_notes_tdy_ly
 * @property string $delivery_notes_py1
 * @property string $delivery_notes_py2
 * @property string $delivery_notes_py3
 * @property string $delivery_notes_py4
 * @property string $delivery_notes_py5
 * @property string $delivery_notes_pq1
 * @property string $delivery_notes_pq2
 * @property string $delivery_notes_pq3
 * @property string $delivery_notes_pq4
 * @property string $delivery_notes_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder|GroupOrdersIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupOrdersIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupOrdersIntervals query()
 * @mixin \Eloquent
 */
class GroupOrdersIntervals extends Model
{
    protected $table = 'group_orders_intervals';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
