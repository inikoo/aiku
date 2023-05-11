<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:56:08 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Procurement\SupplierStats
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $number_supplier_products Number supplier products (all excluding discontinued)
 * @property int $number_supplier_deliveries Number supplier deliveries (all excluding discontinued)
 * @property int $supplier_products_count Number supplier products
 * @property int $number_supplier_products_state_creating
 * @property int $number_supplier_products_state_active
 * @property int $number_supplier_products_state_discontinuing
 * @property int $number_supplier_products_state_discontinued
 * @property int $number_supplier_deliveries_state_creating
 * @property int $number_supplier_deliveries_state_dispatched
 * @property int $number_supplier_deliveries_state_received
 * @property int $number_supplier_deliveries_state_checked
 * @property int $number_supplier_deliveries_state_settled
 * @property int $number_supplier_deliveries_status_processing
 * @property int $number_supplier_deliveries_status_settled_placed
 * @property int $number_supplier_deliveries_status_settled_cancelled
 * @property int $number_supplier_products_stock_quantity_status_excess
 * @property int $number_supplier_products_stock_quantity_status_ideal
 * @property int $number_supplier_products_stock_quantity_status_low
 * @property int $number_supplier_products_stock_quantity_status_critical
 * @property int $number_supplier_products_stock_quantity_status_out_of_stock
 * @property int $number_supplier_products_stock_quantity_status_no_applicable
 * @property int $number_purchase_orders Number purchase orders (except cancelled and failed)
 * @property int $number_open_purchase_orders Number purchase orders (except creating, settled)
 * @property int $number_purchase_orders_state_creating
 * @property int $number_purchase_orders_state_submitted
 * @property int $number_purchase_orders_state_confirmed
 * @property int $number_purchase_orders_state_manufactured
 * @property int $number_purchase_orders_state_dispatched
 * @property int $number_purchase_orders_state_received
 * @property int $number_purchase_orders_state_checked
 * @property int $number_purchase_orders_state_settled
 * @property int $number_purchase_orders_status_processing
 * @property int $number_purchase_orders_status_settled_placed
 * @property int $number_purchase_orders_status_settled_no_received
 * @property int $number_purchase_orders_status_settled_cancelled
 * @property int $number_deliveries Number supplier deliveries (except cancelled)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\Supplier $supplier
 * @method static Builder|SupplierStats newModelQuery()
 * @method static Builder|SupplierStats newQuery()
 * @method static Builder|SupplierStats query()
 * @mixin \Eloquent
 */
class SupplierStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'supplier_stats';

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
