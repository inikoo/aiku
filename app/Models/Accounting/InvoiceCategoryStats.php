<?php
/*
 * author Arya Permana - Kirin
 * created on 28-10-2024-11h-11m
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
 * @property int $number_invoices
 * @property int $number_customers
 * @property int $number_invoice_category_state_in_process
 * @property int $number_invoice_category_state_active
 * @property int $number_invoice_category_state_closed
 * @property int $number_invoice_category_state_cooldown
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accounting\InvoiceCategory $invoiceCategory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategoryStats query()
 * @mixin \Eloquent
 */
class InvoiceCategoryStats extends Model
{
    protected $table = 'invoice_category_stats';

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
