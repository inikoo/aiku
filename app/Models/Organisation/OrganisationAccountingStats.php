<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Organisation;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organisation\OrganisationAccountingStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_payment_service_providers
 * @property int $number_payment_accounts
 * @property int $number_payment_records
 * @property int $number_payments
 * @property int $number_refunds
 * @property string $tc_amount organisation currency, amount_successfully_paid-amount_returned
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
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Organisation\Organisation $organisation
 * @method static Builder|OrganisationAccountingStats newModelQuery()
 * @method static Builder|OrganisationAccountingStats newQuery()
 * @method static Builder|OrganisationAccountingStats query()
 * @mixin Eloquent
 */
class OrganisationAccountingStats extends Model
{
    protected $table = 'organisation_accounting_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
