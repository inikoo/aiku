<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:30:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Currency;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $shop_id
 * @property int $payment_account_id
 * @property int $currency_id
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property PaymentAccountShopStateEnum $state
 * @property PaymentAccountTypeEnum|null $type
 * @property-read Currency $currency
 * @property-read \App\Models\Accounting\PaymentAccount $paymentAccount
 * @property-read Shop $shop
 * @property-read \App\Models\Accounting\PaymentAccountShopStats|null $stats
 * @method static Builder<static>|PaymentAccountShop newModelQuery()
 * @method static Builder<static>|PaymentAccountShop newQuery()
 * @method static Builder<static>|PaymentAccountShop query()
 * @mixin Eloquent
 */
class PaymentAccountShop extends Model
{
    protected $table = 'payment_account_shop';

    protected $casts = [
        'data'  => 'array',
        'state' => PaymentAccountShopStateEnum::class,
        'type'  => PaymentAccountTypeEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PaymentAccountShopStats::class);
    }
}
