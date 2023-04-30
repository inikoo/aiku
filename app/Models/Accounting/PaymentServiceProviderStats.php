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
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Payments\PaymentServiceProviderStats
 *
 * @property int $id
 * @property int $payment_service_provider_id
 * @property int $number_accounts
 * @property int $number_payment_records
 * @property int $number_payments
 * @property int $number_refunds
 * @property string $tc_amount tenant currency, amount_successfully_paid-amount_returned
 * @property string $tc_amount_successfully_paid
 * @property string $tc_amount_refunded
 * @property string $gc_amount Group currency, amount_successfully_paid-amount_returned
 * @property string $gc_amount_successfully_paid
 * @property string $gc_amount_refunded
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
 * @property-read \App\Models\Accounting\PaymentServiceProvider $paymentServiceProvider
 * @method static Builder|PaymentServiceProviderStats newModelQuery()
 * @method static Builder|PaymentServiceProviderStats newQuery()
 * @method static Builder|PaymentServiceProviderStats query()
 * @mixin \Eloquent
 */
class PaymentServiceProviderStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'payment_service_provider_stats';

    protected $guarded = [];

    public function paymentServiceProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentServiceProvider::class);
    }
}
