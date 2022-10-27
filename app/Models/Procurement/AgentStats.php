<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 12:07:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Procurement\AgentStats
 *
 * @property int $id
 * @property int $agent_id
 * @property int $number_suppliers
 * @property int $number_active_suppliers
 * @property int $number_products
 * @property int $number_products_state_creating
 * @property int $number_products_state_active
 * @property int $number_products_state_no_available
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_products_stock_quantity_status_surplus
 * @property int $number_products_stock_quantity_status_optimal
 * @property int $number_products_stock_quantity_status_low
 * @property int $number_products_stock_quantity_status_critical
 * @property int $number_products_stock_quantity_status_out_of_stock
 * @property int $number_products_stock_quantity_status_no_applicable
 * @property int $number_purchase_orders
 * @property int $number_purchase_orders_state_in_process
 * @property int $number_purchase_orders_state_submitted
 * @property int $number_purchase_orders_state_confirmed
 * @property int $number_purchase_orders_state_dispatched
 * @property int $number_purchase_orders_state_delivered
 * @property int $number_purchase_orders_state_cancelled
 * @property int $number_deliveries
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\Agent $shop
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberActiveSuppliers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberDeliveries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStateCreating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStateDiscontinued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStateDiscontinuing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStateNoAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusCritical($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusNoApplicable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusOptimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusOutOfStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberProductsStockQuantityStatusSurplus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateDispatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateInProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberPurchaseOrdersStateSubmitted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereNumberSuppliers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AgentStats extends Model
{
    protected $table = 'agent_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
