<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Payments\PaymentAccountStats
 *
 * @property int $id
 * @property int $payment_account_id
 * @property int $number_payments
 * @property int $number_payments_type_payment
 * @property int $number_payments_type_refund
 * @property int $number_payments_state_in_process
 * @property int $number_payments_state_approving
 * @property int $number_payments_state_completed
 * @property int $number_payments_state_cancelled
 * @property int $number_payments_state_error
 * @property int $number_payments_state_declined
 * @property int $number_payments_type_payment_state_in_process
 * @property int $number_payments_type_payment_state_approving
 * @property int $number_payments_type_payment_state_completed
 * @property int $number_payments_type_payment_state_cancelled
 * @property int $number_payments_type_payment_state_error
 * @property int $number_payments_type_payment_state_declined
 * @property int $number_payments_type_refund_state_in_process
 * @property int $number_payments_type_refund_state_approving
 * @property int $number_payments_type_refund_state_completed
 * @property int $number_payments_type_refund_state_cancelled
 * @property int $number_payments_type_refund_state_error
 * @property int $number_payments_type_refund_state_declined
 * @property string $sales_org_currency_ organisation currency, amount_successfully_paid-amount_returned
 * @property string $sales_org_currency_successfully_paid
 * @property string $sales_org_currency_refunded
 * @property string $sales_grp_currency Group currency, amount_successfully_paid-amount_returned
 * @property string $sales_grp_currencysuccessfully_paid
 * @property string $sales_grp_currencyrefunded
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accounting\PaymentAccount $paymentAccount
 * @method static Builder<static>|PaymentAccountStats newModelQuery()
 * @method static Builder<static>|PaymentAccountStats newQuery()
 * @method static Builder<static>|PaymentAccountStats query()
 * @mixin Eloquent
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
