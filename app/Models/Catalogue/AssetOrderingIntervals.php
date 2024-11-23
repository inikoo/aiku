<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Nov 2024 10:44:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $asset_id
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
 * @property string $invoices_lm
 * @property string $invoices_lw
 * @property string $invoices_all_ly
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
 * @property string $invoices_lm_ly
 * @property string $invoices_lw_ly
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
 * @property string $refunds_all
 * @property string $refunds_1y
 * @property string $refunds_1q
 * @property string $refunds_1m
 * @property string $refunds_1w
 * @property string $refunds_3d
 * @property string $refunds_1d
 * @property string $refunds_ytd
 * @property string $refunds_qtd
 * @property string $refunds_mtd
 * @property string $refunds_wtd
 * @property string $refunds_lm
 * @property string $refunds_lw
 * @property string $refunds_all_ly
 * @property string $refunds_1y_ly
 * @property string $refunds_1q_ly
 * @property string $refunds_1m_ly
 * @property string $refunds_1w_ly
 * @property string $refunds_3d_ly
 * @property string $refunds_1d_ly
 * @property string $refunds_ytd_ly
 * @property string $refunds_qtd_ly
 * @property string $refunds_mtd_ly
 * @property string $refunds_wtd_ly
 * @property string $refunds_lm_ly
 * @property string $refunds_lw_ly
 * @property string $refunds_py1
 * @property string $refunds_py2
 * @property string $refunds_py3
 * @property string $refunds_py4
 * @property string $refunds_py5
 * @property string $refunds_pq1
 * @property string $refunds_pq2
 * @property string $refunds_pq3
 * @property string $refunds_pq4
 * @property string $refunds_pq5
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
 * @property string $orders_lm
 * @property string $orders_lw
 * @property string $orders_all_ly
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
 * @property string $orders_lm_ly
 * @property string $orders_lw_ly
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
 * @property string $delivery_notes_lm
 * @property string $delivery_notes_lw
 * @property string $delivery_notes_all_ly
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
 * @property string $delivery_notes_lm_ly
 * @property string $delivery_notes_lw_ly
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
 * @property string $customers_invoiced_all
 * @property string $customers_invoiced_1y
 * @property string $customers_invoiced_1q
 * @property string $customers_invoiced_1m
 * @property string $customers_invoiced_1w
 * @property string $customers_invoiced_3d
 * @property string $customers_invoiced_1d
 * @property string $customers_invoiced_ytd
 * @property string $customers_invoiced_qtd
 * @property string $customers_invoiced_mtd
 * @property string $customers_invoiced_wtd
 * @property string $customers_invoiced_lm
 * @property string $customers_invoiced_lw
 * @property string $customers_invoiced_all_ly
 * @property string $customers_invoiced_1y_ly
 * @property string $customers_invoiced_1q_ly
 * @property string $customers_invoiced_1m_ly
 * @property string $customers_invoiced_1w_ly
 * @property string $customers_invoiced_3d_ly
 * @property string $customers_invoiced_1d_ly
 * @property string $customers_invoiced_ytd_ly
 * @property string $customers_invoiced_qtd_ly
 * @property string $customers_invoiced_mtd_ly
 * @property string $customers_invoiced_wtd_ly
 * @property string $customers_invoiced_lm_ly
 * @property string $customers_invoiced_lw_ly
 * @property string $customers_invoiced_py1
 * @property string $customers_invoiced_py2
 * @property string $customers_invoiced_py3
 * @property string $customers_invoiced_py4
 * @property string $customers_invoiced_py5
 * @property string $customers_invoiced_pq1
 * @property string $customers_invoiced_pq2
 * @property string $customers_invoiced_pq3
 * @property string $customers_invoiced_pq4
 * @property string $customers_invoiced_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetOrderingIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetOrderingIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetOrderingIntervals query()
 * @mixin \Eloquent
 */
class AssetOrderingIntervals extends Model
{
    protected $guarded = [];

}
