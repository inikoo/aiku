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
