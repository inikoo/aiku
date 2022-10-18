<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 18:16:08 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantSalesStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_customers
 * @property int $number_customers_state_in_process
 * @property int $number_customers_state_active
 * @property int $number_customers_state_losing
 * @property int $number_customers_state_lost
 * @property int $number_customers_state_registered
 * @property int $number_customers_trade_state_none
 * @property int $number_customers_trade_state_one
 * @property int $number_customers_trade_state_many
 * @property int $number_orders
 * @property int $number_orders_state_in_basket
 * @property int $number_orders_state_in_process
 * @property int $number_orders_state_in_warehouse
 * @property int $number_orders_state_packed
 * @property int $number_orders_state_packed_done
 * @property int $number_orders_state_dispatched
 * @property int $number_orders_state_returned
 * @property int $number_orders_state_cancelled
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property int|null $currency_id
 * @property string $all
 * @property string $1y
 * @property string $1q
 * @property string $1m
 * @property string $1w
 * @property string $ytd
 * @property string $qtd
 * @property string $mtd
 * @property string $wtd
 * @property string $lm
 * @property string $lw
 * @property string $yda
 * @property string $tdy
 * @property string $1y_ly
 * @property string $1q_ly
 * @property string $1m_ly
 * @property string $1w_ly
 * @property string $ytd_ly
 * @property string $qtd_ly
 * @property string $mtd_ly
 * @property string $wtd_ly
 * @property string $lm_ly
 * @property string $lw_ly
 * @property string $yda_ly
 * @property string $tdy_ly
 * @property string $py1
 * @property string $py2
 * @property string $py3
 * @property string $py4
 * @property string $py5
 * @property string $pq1
 * @property string $pq2
 * @property string $pq3
 * @property string $pq4
 * @property string $pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static Builder|TenantSalesStats newModelQuery()
 * @method static Builder|TenantSalesStats newQuery()
 * @method static Builder|TenantSalesStats query()
 * @method static Builder|TenantSalesStats where1m($value)
 * @method static Builder|TenantSalesStats where1mLy($value)
 * @method static Builder|TenantSalesStats where1q($value)
 * @method static Builder|TenantSalesStats where1qLy($value)
 * @method static Builder|TenantSalesStats where1w($value)
 * @method static Builder|TenantSalesStats where1wLy($value)
 * @method static Builder|TenantSalesStats where1y($value)
 * @method static Builder|TenantSalesStats where1yLy($value)
 * @method static Builder|TenantSalesStats whereAll($value)
 * @method static Builder|TenantSalesStats whereCreatedAt($value)
 * @method static Builder|TenantSalesStats whereCurrencyId($value)
 * @method static Builder|TenantSalesStats whereId($value)
 * @method static Builder|TenantSalesStats whereLm($value)
 * @method static Builder|TenantSalesStats whereLmLy($value)
 * @method static Builder|TenantSalesStats whereLw($value)
 * @method static Builder|TenantSalesStats whereLwLy($value)
 * @method static Builder|TenantSalesStats whereMtd($value)
 * @method static Builder|TenantSalesStats whereMtdLy($value)
 * @method static Builder|TenantSalesStats whereNumberCustomers($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersStateActive($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersStateInProcess($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersStateLosing($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersStateLost($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersStateRegistered($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersTradeStateMany($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersTradeStateNone($value)
 * @method static Builder|TenantSalesStats whereNumberCustomersTradeStateOne($value)
 * @method static Builder|TenantSalesStats whereNumberInvoices($value)
 * @method static Builder|TenantSalesStats whereNumberInvoicesTypeInvoice($value)
 * @method static Builder|TenantSalesStats whereNumberInvoicesTypeRefund($value)
 * @method static Builder|TenantSalesStats whereNumberOrders($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateCancelled($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateDispatched($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateInBasket($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateInProcess($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateInWarehouse($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStatePacked($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStatePackedDone($value)
 * @method static Builder|TenantSalesStats whereNumberOrdersStateReturned($value)
 * @method static Builder|TenantSalesStats wherePq1($value)
 * @method static Builder|TenantSalesStats wherePq2($value)
 * @method static Builder|TenantSalesStats wherePq3($value)
 * @method static Builder|TenantSalesStats wherePq4($value)
 * @method static Builder|TenantSalesStats wherePq5($value)
 * @method static Builder|TenantSalesStats wherePy1($value)
 * @method static Builder|TenantSalesStats wherePy2($value)
 * @method static Builder|TenantSalesStats wherePy3($value)
 * @method static Builder|TenantSalesStats wherePy4($value)
 * @method static Builder|TenantSalesStats wherePy5($value)
 * @method static Builder|TenantSalesStats whereQtd($value)
 * @method static Builder|TenantSalesStats whereQtdLy($value)
 * @method static Builder|TenantSalesStats whereTdy($value)
 * @method static Builder|TenantSalesStats whereTdyLy($value)
 * @method static Builder|TenantSalesStats whereTenantId($value)
 * @method static Builder|TenantSalesStats whereUpdatedAt($value)
 * @method static Builder|TenantSalesStats whereWtd($value)
 * @method static Builder|TenantSalesStats whereWtdLy($value)
 * @method static Builder|TenantSalesStats whereYda($value)
 * @method static Builder|TenantSalesStats whereYdaLy($value)
 * @method static Builder|TenantSalesStats whereYtd($value)
 * @method static Builder|TenantSalesStats whereYtdLy($value)
 * @mixin \Eloquent
 */
class TenantSalesStats extends Model
{
    protected $table = 'tenant_sales_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
