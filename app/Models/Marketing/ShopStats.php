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
 * @property int $number_departments_state_creating
 * @property int $number_departments_state_active
 * @property int $number_departments_state_suspended
 * @property int $number_departments_state_discontinuing
 * @property int $number_departments_state_discontinued
 * @property int $number_families
 * @property int $number_families_state_creating
 * @property int $number_families_state_active
 * @property int $number_families_state_suspended
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_products
 * @property int $number_products_state_creating
 * @property int $number_products_state_active
 * @property int $number_products_state_suspended
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_orders
 * @property int $number_orders_state_in_basket
 * @property int $number_orders_state_in_process
 * @property int $number_orders_state_in_warehouse
 * @property int $number_orders_state_packed
 * @property int $number_orders_state_packed_done
 * @property int $number_orders_state_dispatched
 * @property int $number_orders_state_returned
 * @property int $number_orders_state_cancelled
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_deliveries_state_ready_to_be_picked
 * @property int $number_deliveries_state_picker_assigned
 * @property int $number_deliveries_state_picking
 * @property int $number_deliveries_state_picked
 * @property int $number_deliveries_state_packing
 * @property int $number_deliveries_state_packed
 * @property int $number_deliveries_state_packed_done
 * @property int $number_deliveries_state_approved
 * @property int $number_deliveries_state_dispatched
 * @property int $number_deliveries_state_cancelled
 * @property int $number_deliveries_state_cancelled_to_restock
 * @property int $number_invoices
 * @property int $number_invoices_type_invoice
 * @property int $number_invoices_type_refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Shop $shop
 * @method static Builder|ShopStats newModelQuery()
 * @method static Builder|ShopStats newQuery()
 * @method static Builder|ShopStats query()
 * @method static Builder|ShopStats whereCreatedAt($value)
 * @method static Builder|ShopStats whereId($value)
 * @method static Builder|ShopStats whereNumberCustomers($value)
 * @method static Builder|ShopStats whereNumberCustomersStateActive($value)
 * @method static Builder|ShopStats whereNumberCustomersStateInProcess($value)
 * @method static Builder|ShopStats whereNumberCustomersStateLosing($value)
 * @method static Builder|ShopStats whereNumberCustomersStateLost($value)
 * @method static Builder|ShopStats whereNumberCustomersStateRegistered($value)
 * @method static Builder|ShopStats whereNumberCustomersTradeStateMany($value)
 * @method static Builder|ShopStats whereNumberCustomersTradeStateNone($value)
 * @method static Builder|ShopStats whereNumberCustomersTradeStateOne($value)
 * @method static Builder|ShopStats whereNumberDeliveries($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStateApproved($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStateCancelled($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStateCancelledToRestock($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStateDispatched($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePacked($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePackedDone($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePacking($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePicked($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePickerAssigned($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStatePicking($value)
 * @method static Builder|ShopStats whereNumberDeliveriesStateReadyToBePicked($value)
 * @method static Builder|ShopStats whereNumberDeliveriesTypeOrder($value)
 * @method static Builder|ShopStats whereNumberDeliveriesTypeReplacement($value)
 * @method static Builder|ShopStats whereNumberDepartments($value)
 * @method static Builder|ShopStats whereNumberDepartmentsStateActive($value)
 * @method static Builder|ShopStats whereNumberDepartmentsStateCreating($value)
 * @method static Builder|ShopStats whereNumberDepartmentsStateDiscontinued($value)
 * @method static Builder|ShopStats whereNumberDepartmentsStateDiscontinuing($value)
 * @method static Builder|ShopStats whereNumberDepartmentsStateSuspended($value)
 * @method static Builder|ShopStats whereNumberFamilies($value)
 * @method static Builder|ShopStats whereNumberFamiliesStateActive($value)
 * @method static Builder|ShopStats whereNumberFamiliesStateCreating($value)
 * @method static Builder|ShopStats whereNumberFamiliesStateDiscontinued($value)
 * @method static Builder|ShopStats whereNumberFamiliesStateDiscontinuing($value)
 * @method static Builder|ShopStats whereNumberFamiliesStateSuspended($value)
 * @method static Builder|ShopStats whereNumberInvoices($value)
 * @method static Builder|ShopStats whereNumberInvoicesTypeInvoice($value)
 * @method static Builder|ShopStats whereNumberInvoicesTypeRefund($value)
 * @method static Builder|ShopStats whereNumberOrders($value)
 * @method static Builder|ShopStats whereNumberOrdersStateCancelled($value)
 * @method static Builder|ShopStats whereNumberOrdersStateDispatched($value)
 * @method static Builder|ShopStats whereNumberOrdersStateInBasket($value)
 * @method static Builder|ShopStats whereNumberOrdersStateInProcess($value)
 * @method static Builder|ShopStats whereNumberOrdersStateInWarehouse($value)
 * @method static Builder|ShopStats whereNumberOrdersStatePacked($value)
 * @method static Builder|ShopStats whereNumberOrdersStatePackedDone($value)
 * @method static Builder|ShopStats whereNumberOrdersStateReturned($value)
 * @method static Builder|ShopStats whereNumberOrphanFamilies($value)
 * @method static Builder|ShopStats whereNumberProducts($value)
 * @method static Builder|ShopStats whereNumberProductsStateActive($value)
 * @method static Builder|ShopStats whereNumberProductsStateCreating($value)
 * @method static Builder|ShopStats whereNumberProductsStateDiscontinued($value)
 * @method static Builder|ShopStats whereNumberProductsStateDiscontinuing($value)
 * @method static Builder|ShopStats whereNumberProductsStateSuspended($value)
 * @method static Builder|ShopStats whereShopId($value)
 * @method static Builder|ShopStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShopStats extends Model
{

    protected $table = 'shop_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }


}
