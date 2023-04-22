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
 * @property int $number_products all excluding discontinued
 * @property int $number_products_state_creating
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_products_stock_quantity_status_excess
 * @property int $number_products_stock_quantity_status_ideal
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
