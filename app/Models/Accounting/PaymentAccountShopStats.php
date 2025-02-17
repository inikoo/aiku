<?php
/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-15h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/
namespace App\Models\Accounting;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAccountShopStats extends Model
{
    protected $table = 'payment_account_shop_stats';

    protected $guarded = [];

    public function paymentAccountShop(): BelongsTo
    {
        return $this->belongsTo(PaymentAccountShop::class);
    }
}
