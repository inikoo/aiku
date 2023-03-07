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
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

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
 * @mixin \Eloquent
 */
class ShopAccountingStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'shop_accounting_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
