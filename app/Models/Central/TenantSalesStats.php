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
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

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
 * @property int $number_orders_state_finalised
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
 * @mixin \Eloquent
 */
class TenantSalesStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_sales_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
