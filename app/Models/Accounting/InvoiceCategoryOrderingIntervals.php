<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Nov 2024 10:41:55 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $invoice_category_id
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryOrderingIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryOrderingIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryOrderingIntervals query()
 * @mixin \Eloquent
 */
class InvoiceCategoryOrderingIntervals extends Model
{
    protected $table = 'invoice_category_ordering_intervals';
    protected $guarded = [];

}
