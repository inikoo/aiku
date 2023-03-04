<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 15:43:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Sales\PaymentAccountShop
 *
 * @property int $id
 * @property int $shop_id
 * @property int $payment_account_id
 * @property int $currency_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|PaymentAccountShop newModelQuery()
 * @method static Builder|PaymentAccountShop newQuery()
 * @method static Builder|PaymentAccountShop query()
 * @mixin \Eloquent
 */
class PaymentAccountShop extends Pivot
{
    use UsesTenantConnection;

    public $incrementing = true;

    protected $casts = [
        'data'     => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

}
