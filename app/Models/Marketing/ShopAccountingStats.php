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
 * @method static Builder|ShopAccountingStats whereNumberApprovingPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberApprovingPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberApprovingRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberCancelledPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberCancelledPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberCancelledRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberCompletedPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberCompletedPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberCompletedRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberDeclinedPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberDeclinedPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberDeclinedRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberErrorPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberErrorPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberErrorRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberInProcessPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberInProcessPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberInProcessRefunds($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentAccounts($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentRecords($value)
 * @method static Builder|ShopAccountingStats whereNumberPaymentServiceProviders($value)
 * @method static Builder|ShopAccountingStats whereNumberPayments($value)
 * @method static Builder|ShopAccountingStats whereNumberRefunds($value)
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
