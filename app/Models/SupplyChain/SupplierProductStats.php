<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 19:33:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SupplyChain\SupplierProductStats
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property int $number_purchase_orders
 * @property int $number_current_purchase_orders Number purchase orders (except: cancelled and not_received)
 * @property int $number_open_purchase_orders Number purchase orders (except: in_process,settled,cancelled,not_received)
 * @property int $number_purchase_orders_state_in_process
 * @property int $number_purchase_orders_state_submitted
 * @property int $number_purchase_orders_state_confirmed
 * @property int $number_purchase_orders_state_settled
 * @property int $number_purchase_orders_state_cancelled
 * @property int $number_purchase_orders_state_not_received
 * @property int $number_purchase_orders_delivery_status_in_process
 * @property int $number_purchase_orders_delivery_status_confirmed
 * @property int $number_purchase_orders_delivery_status_ready_to_ship
 * @property int $number_purchase_orders_delivery_status_dispatched
 * @property int $number_purchase_orders_delivery_status_received
 * @property int $number_purchase_orders_delivery_status_checked
 * @property int $number_purchase_orders_delivery_status_placed
 * @property int $number_purchase_orders_delivery_status_cancelled
 * @property int $number_purchase_orders_delivery_status_not_received
 * @property int $number_stock_deliveries Number supplier deliveries
 * @property int $number_current_stock_deliveries Number supplier deliveries (except: cancelled and not_received)
 * @property int $number_stock_deliveries_state_in_process
 * @property int $number_stock_deliveries_state_confirmed
 * @property int $number_stock_deliveries_state_ready_to_ship
 * @property int $number_stock_deliveries_state_dispatched
 * @property int $number_stock_deliveries_state_received
 * @property int $number_stock_deliveries_state_checked
 * @property int $number_stock_deliveries_state_placed
 * @property int $number_stock_deliveries_state_cancelled
 * @property int $number_stock_deliveries_state_not_received
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SupplyChain\SupplierProduct $supplierProduct
 * @method static Builder<static>|SupplierProductStats newModelQuery()
 * @method static Builder<static>|SupplierProductStats newQuery()
 * @method static Builder<static>|SupplierProductStats query()
 * @mixin Eloquent
 */
class SupplierProductStats extends Model
{
    protected $table = 'supplier_product_stats';

    protected $guarded = [];


    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
