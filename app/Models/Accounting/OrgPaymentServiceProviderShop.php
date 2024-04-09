<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:30:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Accounting\OrgPaymentServiceProviderShop
 *
 * @property int $id
 * @property int $shop_id
 * @property int $org_payment_service_provider_id
 * @property int $currency_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OrgPaymentServiceProviderShop newModelQuery()
 * @method static Builder|OrgPaymentServiceProviderShop newQuery()
 * @method static Builder|OrgPaymentServiceProviderShop query()
 * @mixin Eloquent
 */
class OrgPaymentServiceProviderShop extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'data'     => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];
}
