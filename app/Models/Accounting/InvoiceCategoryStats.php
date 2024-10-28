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
