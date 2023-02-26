<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Payments\PaymentAccountStats
 *
 * @property int $id
 * @property int $payment_account_id
 * @property int $number_payments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|PaymentAccountStats newModelQuery()
 * @method static Builder|PaymentAccountStats newQuery()
 * @method static Builder|PaymentAccountStats query()
 * @method static Builder|PaymentAccountStats whereCreatedAt($value)
 * @method static Builder|PaymentAccountStats whereId($value)
 * @method static Builder|PaymentAccountStats whereNumberPayments($value)
 * @method static Builder|PaymentAccountStats wherePaymentAccountId($value)
 * @method static Builder|PaymentAccountStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentAccountStats extends Model
{

    protected $table = 'payment_account_stats';

    protected $guarded = [];

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }
}
