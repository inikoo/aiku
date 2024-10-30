<?php
/*
 * author Arya Permana - Kirin
 * created on 28-10-2024-11h-20m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $invoice_category_id
 * @property string $amount_all
 * @property string $amount_1y
 * @property string $amount_1q
 * @property string $amount_1m
 * @property string $amount_1w
 * @property string $amount_ytd
 * @property string $amount_qtd
 * @property string $amount_mtd
 * @property string $amount_wtd
 * @property string $amount_lm
 * @property string $amount_lw
 * @property string $amount_yda
 * @property string $amount_tdy
 * @property string $amount_all_ly
 * @property string $amount_1y_ly
 * @property string $amount_1q_ly
 * @property string $amount_1m_ly
 * @property string $amount_1w_ly
 * @property string $amount_ytd_ly
 * @property string $amount_qtd_ly
 * @property string $amount_mtd_ly
 * @property string $amount_wtd_ly
 * @property string $amount_lm_ly
 * @property string $amount_lw_ly
 * @property string $amount_yda_ly
 * @property string $amount_tdy_ly
 * @property string $amount_py1
 * @property string $amount_py2
 * @property string $amount_py3
 * @property string $amount_py4
 * @property string $amount_py5
 * @property string $amount_pq1
 * @property string $amount_pq2
 * @property string $amount_pq3
 * @property string $amount_pq4
 * @property string $amount_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accounting\InvoiceCategory $invoiceCategory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategorySalesIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategorySalesIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategorySalesIntervals query()
 * @mixin \Eloquent
 */
class InvoiceCategorySalesIntervals extends Model
{
    protected $table = 'invoice_category_sales_intervals';

    protected $casts = [
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

    public function invoiceCategory(): BelongsTo
    {
        return $this->belongsTo(InvoiceCategory::class, 'invoice_category_id');
    }
}
