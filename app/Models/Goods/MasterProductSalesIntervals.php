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
 * App\Models\MasterProductSalesIntervals
 *
 * @property int $id
 * @property int $master_product_id
 * @property string $sales_grp_currency_all
 * @property string $sales_grp_currency_1y
 * @property string $sales_grp_currency_1q
 * @property string $sales_grp_currency_1m
 * @property string $sales_grp_currency_1w
 * @property string $sales_grp_currency_3d
 * @property string $sales_grp_currency_1d
 * @property string $sales_grp_currency_ytd
 * @property string $sales_grp_currency_qtd
 * @property string $sales_grp_currency_mtd
 * @property string $sales_grp_currency_wtd
 * @property string $sales_grp_currency_tdy
 * @property string $sales_grp_currency_lm
 * @property string $sales_grp_currency_lw
 * @property string $sales_grp_currency_ld
 * @property string $sales_grp_currency_1y_ly
 * @property string $sales_grp_currency_1q_ly
 * @property string $sales_grp_currency_1m_ly
 * @property string $sales_grp_currency_1w_ly
 * @property string $sales_grp_currency_3d_ly
 * @property string $sales_grp_currency_1d_ly
 * @property string $sales_grp_currency_ytd_ly
 * @property string $sales_grp_currency_qtd_ly
 * @property string $sales_grp_currency_mtd_ly
 * @property string $sales_grp_currency_wtd_ly
 * @property string $sales_grp_currency_tdy_ly
 * @property string $sales_grp_currency_lm_ly
 * @property string $sales_grp_currency_lw_ly
 * @property string $sales_grp_currency_ld_ly
 * @property string $sales_grp_currency_py1
 * @property string $sales_grp_currency_py2
 * @property string $sales_grp_currency_py3
 * @property string $sales_grp_currency_py4
 * @property string $sales_grp_currency_py5
 * @property string $sales_grp_currency_pq1
 * @property string $sales_grp_currency_pq2
 * @property string $sales_grp_currency_pq3
 * @property string $sales_grp_currency_pq4
 * @property string $sales_grp_currency_pq5
 * @property string $invoices_all
 * @property string $invoices_1y
 * @property string $invoices_1q
 * @property string $invoices_1m
 * @property string $invoices_1w
 * @property string $invoices_3d
 * @property string $invoices_1d
 * @property string $invoices_ytd
 * @property string $invoices_qtd
 * @property string $invoices_mtd
 * @property string $invoices_wtd
 * @property string $invoices_tdy
 * @property string $invoices_lm
 * @property string $invoices_lw
 * @property string $invoices_ld
 * @property string $invoices_1y_ly
 * @property string $invoices_1q_ly
 * @property string $invoices_1m_ly
 * @property string $invoices_1w_ly
 * @property string $invoices_3d_ly
 * @property string $invoices_1d_ly
 * @property string $invoices_ytd_ly
 * @property string $invoices_qtd_ly
 * @property string $invoices_mtd_ly
 * @property string $invoices_wtd_ly
 * @property string $invoices_tdy_ly
 * @property string $invoices_lm_ly
 * @property string $invoices_lw_ly
 * @property string $invoices_ld_ly
 * @property string $invoices_py1
 * @property string $invoices_py2
 * @property string $invoices_py3
 * @property string $invoices_py4
 * @property string $invoices_py5
 * @property string $invoices_pq1
 * @property string $invoices_pq2
 * @property string $invoices_pq3
 * @property string $invoices_pq4
 * @property string $invoices_pq5
 * @property string $orders_all
 * @property string $orders_1y
 * @property string $orders_1q
 * @property string $orders_1m
 * @property string $orders_1w
 * @property string $orders_3d
 * @property string $orders_1d
 * @property string $orders_ytd
 * @property string $orders_qtd
 * @property string $orders_mtd
 * @property string $orders_wtd
 * @property string $orders_tdy
 * @property string $orders_lm
 * @property string $orders_lw
 * @property string $orders_ld
 * @property string $orders_1y_ly
 * @property string $orders_1q_ly
 * @property string $orders_1m_ly
 * @property string $orders_1w_ly
 * @property string $orders_3d_ly
 * @property string $orders_1d_ly
 * @property string $orders_ytd_ly
 * @property string $orders_qtd_ly
 * @property string $orders_mtd_ly
 * @property string $orders_wtd_ly
 * @property string $orders_tdy_ly
 * @property string $orders_lm_ly
 * @property string $orders_lw_ly
 * @property string $orders_ld_ly
 * @property string $orders_py1
 * @property string $orders_py2
 * @property string $orders_py3
 * @property string $orders_py4
 * @property string $orders_py5
 * @property string $orders_pq1
 * @property string $orders_pq2
 * @property string $orders_pq3
 * @property string $orders_pq4
 * @property string $orders_pq5
 * @property string $delivery_notes_all
 * @property string $delivery_notes_1y
 * @property string $delivery_notes_1q
 * @property string $delivery_notes_1m
 * @property string $delivery_notes_1w
 * @property string $delivery_notes_3d
 * @property string $delivery_notes_1d
 * @property string $delivery_notes_ytd
 * @property string $delivery_notes_qtd
 * @property string $delivery_notes_mtd
 * @property string $delivery_notes_wtd
 * @property string $delivery_notes_tdy
 * @property string $delivery_notes_lm
 * @property string $delivery_notes_lw
 * @property string $delivery_notes_ld
 * @property string $delivery_notes_1y_ly
 * @property string $delivery_notes_1q_ly
 * @property string $delivery_notes_1m_ly
 * @property string $delivery_notes_1w_ly
 * @property string $delivery_notes_3d_ly
 * @property string $delivery_notes_1d_ly
 * @property string $delivery_notes_ytd_ly
 * @property string $delivery_notes_qtd_ly
 * @property string $delivery_notes_mtd_ly
 * @property string $delivery_notes_wtd_ly
 * @property string $delivery_notes_tdy_ly
 * @property string $delivery_notes_lm_ly
 * @property string $delivery_notes_lw_ly
 * @property string $delivery_notes_ld_ly
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
 * @property string $customers_all
 * @property string $customers_1y
 * @property string $customers_1q
 * @property string $customers_1m
 * @property string $customers_1w
 * @property string $customers_3d
 * @property string $customers_1d
 * @property string $customers_ytd
 * @property string $customers_qtd
 * @property string $customers_mtd
 * @property string $customers_wtd
 * @property string $customers_tdy
 * @property string $customers_lm
 * @property string $customers_lw
 * @property string $customers_ld
 * @property string $customers_1y_ly
 * @property string $customers_1q_ly
 * @property string $customers_1m_ly
 * @property string $customers_1w_ly
 * @property string $customers_3d_ly
 * @property string $customers_1d_ly
 * @property string $customers_ytd_ly
 * @property string $customers_qtd_ly
 * @property string $customers_mtd_ly
 * @property string $customers_wtd_ly
 * @property string $customers_tdy_ly
 * @property string $customers_lm_ly
 * @property string $customers_lw_ly
 * @property string $customers_ld_ly
 * @property string $customers_py1
 * @property string $customers_py2
 * @property string $customers_py3
 * @property string $customers_py4
 * @property string $customers_py5
 * @property string $customers_pq1
 * @property string $customers_pq2
 * @property string $customers_pq3
 * @property string $customers_pq4
 * @property string $customers_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Goods\MasterProduct $masterProduct
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
