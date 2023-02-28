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
 * @property int $number_payment_records_state_in_process
 * @property int $number_payments_state_in_process
 * @property int $number_refunds_state_in_process
 * @property int $number_payment_records_state_approving
 * @property int $number_payments_state_approving
 * @property int $number_refunds_state_approving
 * @property int $number_payment_records_state_completed
 * @property int $number_payments_state_completed
 * @property int $number_refunds_state_completed
 * @property int $number_payment_records_state_cancelled
 * @property int $number_payments_state_cancelled
 * @property int $number_refunds_state_cancelled
 * @property int $number_payment_records_state_error
 * @property int $number_payments_state_error
 * @property int $number_refunds_state_error
 * @property int $number_payment_records_state_declined
 * @property int $number_payments_state_declined
 * @property int $number_refunds_state_declined
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
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecords($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateApproving($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateCancelled($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateCompleted($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateDeclined($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateError($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentRecordsStateInProcess($value)
 * @method static Builder|PaymentAccountStats whereNumberPayments($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateApproving($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateCancelled($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateCompleted($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateDeclined($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateError($value)
 * @method static Builder|PaymentAccountStats whereNumberPaymentsStateInProcess($value)
 * @method static Builder|PaymentAccountStats whereNumberRefunds($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateApproving($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateCancelled($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateCompleted($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateDeclined($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateError($value)
 * @method static Builder|PaymentAccountStats whereNumberRefundsStateInProcess($value)
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
