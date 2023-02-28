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
 * App\Models\Payments\PaymentServiceProviderStats
 *
 * @property int $id
 * @property int $payment_service_provider_id
 * @property int $number_accounts
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
 * @property-read \App\Models\Accounting\PaymentServiceProvider $paymentServiceProvider
 * @method static Builder|PaymentServiceProviderStats newModelQuery()
 * @method static Builder|PaymentServiceProviderStats newQuery()
 * @method static Builder|PaymentServiceProviderStats query()
 * @method static Builder|PaymentServiceProviderStats whereCreatedAt($value)
 * @method static Builder|PaymentServiceProviderStats whereDcAmount($value)
 * @method static Builder|PaymentServiceProviderStats whereDcAmountRefunded($value)
 * @method static Builder|PaymentServiceProviderStats whereDcAmountSuccessfullyPaid($value)
 * @method static Builder|PaymentServiceProviderStats whereId($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberAccounts($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberApprovingPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberApprovingPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberApprovingRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCancelledPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCancelledPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCancelledRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCompletedPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCompletedPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberCompletedRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberDeclinedPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberDeclinedPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberDeclinedRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberErrorPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberErrorPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberErrorRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberInProcessPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberInProcessPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberInProcessRefunds($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberPaymentRecords($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberPayments($value)
 * @method static Builder|PaymentServiceProviderStats whereNumberRefunds($value)
 * @method static Builder|PaymentServiceProviderStats wherePaymentServiceProviderId($value)
 * @method static Builder|PaymentServiceProviderStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentServiceProviderStats extends Model
{
    protected $table = 'payment_service_provider_stats';

    protected $guarded = [];

    public function paymentServiceProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentServiceProvider::class);
    }
}
