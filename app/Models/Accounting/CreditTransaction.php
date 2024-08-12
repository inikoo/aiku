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
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $top_up_id
 * @property int|null $payment_id
 * @property string $type
 * @property \Illuminate\Support\Carbon $date
 * @property string $amount
 * @property string|null $running_amount
 * @property int $currency_id
 * @property string $grp_exchange
 * @property string $org_exchange
 * @property string $grp_amount
 * @property string $org_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read Currency $currency
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
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
        'data'           => 'array',
        'date'           => 'datetime',
        'amount'         => 'decimal:2',
        'running_amount' => 'decimal:2',
        'grp_exchange'   => 'decimal:4',
        'org_exchange'   => 'decimal:4',
        'grp_amount'     => 'decimal:2',
        'org_amount'     => 'decimal:2',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

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
