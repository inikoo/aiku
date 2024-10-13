<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Accounting\InvoiceStats
 *
 * @property-read \App\Models\Accounting\Invoice|null $invoice
 * @method static Builder|InvoiceStats newModelQuery()
 * @method static Builder|InvoiceStats newQuery()
 * @method static Builder|InvoiceStats query()
 * @mixin Eloquent
 */
class InvoiceStats extends Model
{
    protected $table   = 'invoice_stats';
    protected $guarded = [];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
