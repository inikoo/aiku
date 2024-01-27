<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 11:39:03 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SysAdmin\GroupAccountingStats
 *
 * @property int $id
 * @property int $group_id
 * @property int $number_payment_service_providers
 * @property int $number_payment_service_providers_type_account
 * @property int $number_payment_service_providers_type_cash
 * @property int $number_payment_service_providers_type_bank
 * @property int $number_payment_service_providers_type_electronic_payment_servic
 * @property int $number_payment_service_providers_type_electronic_banking_e_paym
 * @property int $number_payment_service_providers_type_cash_on_delivery
 * @property int $number_payment_service_providers_type_buy_now_pay_later
 * @property int $number_payment_accounts
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
 * @property string $oc_amount organisation currency, amount_successfully_paid-amount_returned
 * @property string $oc_amount_successfully_paid
 * @property string $oc_amount_refunded
 * @property string $gc_amount Group currency, amount_successfully_paid-amount_returned
 * @property string $gc_amount_successfully_paid
 * @property string $gc_amount_refunded
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAccountingStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAccountingStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAccountingStats query()
 * @mixin \Eloquent
 */
class GroupAccountingStats extends Model
{
    protected $table = 'group_accounting_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
