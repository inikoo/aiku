<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Aug 2024 10:38:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Models\Helpers\Currency;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read Currency|null $currency
 * @property-read \App\Models\CRM\Customer|null $customer
 * @property-read \App\Models\SysAdmin\Group|null $group
 * @property-read \App\Models\SysAdmin\Organisation|null $organisation
 * @property-read \App\Models\Accounting\Payment|null $payment
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Accounting\TopUp|null $topUp
 * @method static \Illuminate\Database\Eloquent\Builder|CreditTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditTransaction query()
 * @mixin \Eloquent
 */
class CreditTransaction extends Model
{
    use InCustomer;

    protected $casts = [
        'data'                        => 'array',
        'date'                        => 'datetime',
        'amount'                      => 'decimal:2',
        'running_amount'              => 'decimal:2',
        'grp_exchange'                => 'decimal:4',
        'org_exchange'                => 'decimal:4',
        'grp_amount'                  => 'decimal:2',
        'org_amount'                  => 'decimal:2',
        'fetched_at'                  => 'datetime',
        'last_fetched_at'             => 'datetime',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function topUp(): BelongsTo
    {
        return $this->belongsTo(TopUp::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }


}
