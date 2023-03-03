<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:57:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\TenantAccountingStats
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $number_payment_service_providers
 * @property int $number_payment_accounts
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
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|TenantAccountingStats newModelQuery()
 * @method static Builder|TenantAccountingStats newQuery()
 * @method static Builder|TenantAccountingStats query()
 * @method static Builder|TenantAccountingStats whereCreatedAt($value)
 * @method static Builder|TenantAccountingStats whereDcAmount($value)
 * @method static Builder|TenantAccountingStats whereDcAmountRefunded($value)
 * @method static Builder|TenantAccountingStats whereDcAmountSuccessfullyPaid($value)
 * @method static Builder|TenantAccountingStats whereId($value)
 * @method static Builder|TenantAccountingStats whereNumberInvoices($value)
 * @method static Builder|TenantAccountingStats whereNumberInvoicesTypeInvoice($value)
 * @method static Builder|TenantAccountingStats whereNumberInvoicesTypeRefund($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentAccounts($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecords($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateApproving($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateCancelled($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateCompleted($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateDeclined($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateError($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentRecordsStateInProcess($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentServiceProviders($value)
 * @method static Builder|TenantAccountingStats whereNumberPayments($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateApproving($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateCancelled($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateCompleted($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateDeclined($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateError($value)
 * @method static Builder|TenantAccountingStats whereNumberPaymentsStateInProcess($value)
 * @method static Builder|TenantAccountingStats whereNumberRefunds($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateApproving($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateCancelled($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateCompleted($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateDeclined($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateError($value)
 * @method static Builder|TenantAccountingStats whereNumberRefundsStateInProcess($value)
 * @method static Builder|TenantAccountingStats whereTenantId($value)
 * @method static Builder|TenantAccountingStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TenantAccountingStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_accounting_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
