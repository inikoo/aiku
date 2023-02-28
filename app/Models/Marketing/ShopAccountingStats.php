<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 23:45:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Marketing\ShopAccountingStats
 *
 * @property int $id
 * @property int $shop_id
 * @property int $number_payment_service_providers
 * @property int $number_payment_accounts
 * @property int $number_payment_records
 * @property int $number_payments
 * @property int $number_refunds
 * @property string $amount amount_successfully_paid-amount_returned
 * @property string $amount_successfully_paid
 * @property string $amount_refunded
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
 * @property-read \App\Models\Marketing\Shop $shop
 * @method static Builder|ShopAccountingStats newModelQuery()
 * @method static Builder|ShopAccountingStats newQuery()
 * @method static Builder|ShopAccountingStats query()
 * @method static Builder|ShopAccountingStats whereAmount($value)
 * @method static Builder|ShopAccountingStats whereAmountRefunded($value)
 * @method static Builder|ShopAccountingStats whereAmountSuccessfullyPaid($value)
 * @method static Builder|ShopAccountingStats whereCreatedAt($value)
 * @method static Builder|ShopAccountingStats whereDcAmount($value)
 * @method static Builder|ShopAccountingStats whereDcAmountRefunded($value)
 * @method static Builder|ShopAccountingStats whereDcAmountSuccessfullyPaid($value)
 * @method static Builder|ShopAccountingStats whereId($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentAccounts($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateApproving($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateCancelled($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateCompleted($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateDeclined($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateError($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecordsStateInProcess($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentServiceProviders($value)
 * @method static Builder|ShopAccountingStats whereNumberPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateApproving($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateCancelled($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateCompleted($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateDeclined($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateError($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentsStateInProcess($value)
 * @method static Builder|ShopAccountingStats whereNumberRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateApproving($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateCancelled($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateCompleted($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateDeclined($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateError($value)
 * @method static Builder|ShopAccountingStats whereNumberRefundsStateInProcess($value)
 * @method static Builder|ShopAccountingStats whereShopId($value)
 * @method static Builder|ShopAccountingStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShopAccountingStats extends Model
{

    protected $table = 'shop_accounting_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }


}
