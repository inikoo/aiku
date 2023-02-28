<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Payments\PaymentAccountStats
 *
 * @property int $id
 * @property int $payment_account_id
 * @property int $number_payment_records
 * @property int $number_payments
 * @property int $number_refunds
 * @property string $dc_amount Account currency, amount_successfully_paid-amount_returned
 * @property string $dc_amount_successfully_paid
 * @property string $dc_amount_refunded
 * @property int $number_in_process_payment_records
 * @property int $number_in_process_payments
 * @property int $number_in_process_refunds
 * @property int $number_approving_payment_records
 * @property int $number_approving_payments
 * @property int $number_approving_refunds
 * @property int $number_completed_payment_records
 * @property int $number_completed_payments
 * @property int $number_completed_refunds
 * @property int $number_cancelled_payment_records
 * @property int $number_cancelled_payments
 * @property int $number_cancelled_refunds
 * @property int $number_error_payment_records
 * @property int $number_error_payments
 * @property int $number_error_refunds
 * @property int $number_declined_payment_records
 * @property int $number_declined_payments
 * @property int $number_declined_refunds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accounting\PaymentAccount $paymentAccount
 * @method static Builder|PaymentAccountStats newModelQuery()
 * @method static Builder|PaymentAccountStats newQuery()
 * @method static Builder|PaymentAccountStats query()
 * @method static Builder|PaymentAccountStats whereCreatedAt($value)
 * @method static Builder|PaymentAccountStats whereDcAmount($value)
 * @method static Builder|PaymentAccountStats whereDcAmountRefunded($value)
 * @method static Builder|PaymentAccountStats whereDcAmountSuccessfullyPaid($value)
 * @method static Builder|PaymentAccountStats whereId($value)
 * @method static Builder|PaymentAccountStats whereNumberApprovingPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberApprovingPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberApprovingRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberCancelledPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberCancelledPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberCancelledRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberCompletedPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberCompletedPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberCompletedRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberDeclinedPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberDeclinedPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberDeclinedRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberErrorPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberErrorPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberErrorRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberInProcessPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberInProcessPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberInProcessRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberRefunds($value)
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
