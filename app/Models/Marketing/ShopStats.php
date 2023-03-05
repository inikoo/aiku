<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:41:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\ShopStats
 *
 * @property int $id
 * @property int $shop_id
 * @property int $number_customers
 * @property int $number_customers_state_in_process
 * @property int $number_customers_state_active
 * @property int $number_customers_state_losing
 * @property int $number_customers_state_lost
 * @property int $number_customers_state_registered
 * @property int $number_customers_trade_state_none
 * @property int $number_customers_trade_state_one
 * @property int $number_customers_trade_state_many
 * @property int $number_departments
 * @property int $number_departments_state_in_process
 * @property int $number_departments_state_active
 * @property int $number_departments_state_discontinuing
 * @property int $number_departments_state_discontinued
 * @property int $number_families
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_orders
 * @property int $number_orders_state_in_basket
 * @property int $number_orders_state_in_process
 * @property int $number_orders_state_in_warehouse
 * @property int $number_orders_state_packed
 * @property int $number_orders_state_finalised
 * @property int $number_orders_state_dispatched
 * @property int $number_orders_state_returned
 * @property int $number_orders_state_cancelled
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_deliveries_state_submitted
 * @property int $number_deliveries_state_picker_assigned
 * @property int $number_deliveries_state_picking
 * @property int $number_deliveries_state_picked
 * @property int $number_deliveries_state_packing
 * @property int $number_deliveries_state_packed
 * @property int $number_deliveries_state_finalised
 * @property int $number_deliveries_state_dispatched
 * @property int $number_deliveries_cancelled_at_state_submitted
 * @property int $number_deliveries_cancelled_at_state_picker_assigned
 * @property int $number_deliveries_cancelled_at_state_picking
 * @property int $number_deliveries_cancelled_at_state_picked
 * @property int $number_deliveries_cancelled_at_state_packing
 * @property int $number_deliveries_cancelled_at_state_packed
 * @property int $number_deliveries_cancelled_at_state_finalised
 * @property int $number_deliveries_cancelled_at_state_dispatched
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property int $number_payment_service_providers
 * @property int $number_payment_accounts
 * @property int $number_payments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Shop $shop
 *
 * @method static Builder|ShopStats newModelQuery()
 * @method static Builder|ShopStats newQuery()
 * @method static Builder|ShopStats query()
 *
 * @mixin \Eloquent
 */
class ShopStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'shop_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
