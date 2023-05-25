<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 May 2023 22:39:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Sales\PaymentServiceProviderShop
 *
 * @property int $id
 * @property int $shop_id
 * @property int $payment_service_provider_id
 * @property int $currency_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentServiceProviderShop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentServiceProviderShop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentServiceProviderShop query()
 * @mixin \Eloquent
 */
class PaymentServiceProviderShop extends Pivot
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
