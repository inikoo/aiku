<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 15:43:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Relations\Pivot;

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
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop wherePaymentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentAccountShop whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentAccountShop extends Pivot
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
