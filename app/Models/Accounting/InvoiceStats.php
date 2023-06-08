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
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Accounting\InvoiceStats
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $number_items current number of items
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Accounting\Invoice $invoice
 * @method static Builder|InvoiceStats newModelQuery()
 * @method static Builder|InvoiceStats newQuery()
 * @method static Builder|InvoiceStats query()
 * @mixin Eloquent
 */
class InvoiceStats extends Model
{
    use UsesTenantConnection;

    protected $table   = 'invoice_stats';
    protected $guarded = [];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
