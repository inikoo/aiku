<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:56:08 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Procurement\SupplierStats
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $number_products
 * @property int $number_products_state_in_process
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
 * @property-read \App\Models\Procurement\Supplier $shop
 * @method static Builder|SupplierStats newModelQuery()
 * @method static Builder|SupplierStats newQuery()
 * @method static Builder|SupplierStats query()
 * @method static Builder|SupplierStats whereCreatedAt($value)
 * @method static Builder|SupplierStats whereId($value)
 * @method static Builder|SupplierStats whereNumberDeliveries($value)
 * @method static Builder|SupplierStats whereNumberProducts($value)
 * @method static Builder|SupplierStats whereNumberProductsStateActive($value)
 * @method static Builder|SupplierStats whereNumberProductsStateDiscontinued($value)
 * @method static Builder|SupplierStats whereNumberProductsStateDiscontinuing($value)
 * @method static Builder|SupplierStats whereNumberProductsStateInProcess($value)
 * @method static Builder|SupplierStats whereNumberProductsStateNoAvailable($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusCritical($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusLow($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusNoApplicable($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusOptimal($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusOutOfStock($value)
 * @method static Builder|SupplierStats whereNumberProductsStockQuantityStatusSurplus($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrders($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateCancelled($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateConfirmed($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateDelivered($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateDispatched($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateInProcess($value)
 * @method static Builder|SupplierStats whereNumberPurchaseOrdersStateSubmitted($value)
 * @method static Builder|SupplierStats whereSupplierId($value)
 * @method static Builder|SupplierStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupplierStats extends Model
{
    protected $table = 'supplier_stats';

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
