<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:30:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Accounting\PaymentAccountShop
 *
 * @property int $id
 * @property int $shop_id
 * @property int $payment_account_id
 * @property int $currency_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PaymentAccountShop newModelQuery()
 * @method static Builder|PaymentAccountShop newQuery()
 * @method static Builder|PaymentAccountShop query()
 * @mixin Eloquent
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
