<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:11:49 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrgSupplierStats
 *
 * @property int $id
 * @property int $org_supplier_id
 * @property int $number_org_supplier_products
 * @property int $number_current_org_supplier_products status=true
 * @property int $number_available_org_supplier_products
 * @property int $number_no_available_org_supplier_products only for state=active|discontinuing
 * @property int $number_org_supplier_products_state_active
 * @property int $number_org_supplier_products_state_discontinuing
 * @property int $number_org_supplier_products_state_discontinued
 * @property int $number_purchase_orders
 * @property int $number_purchase_orders_except_cancelled Number purchase orders (except cancelled and failed)
 * @property int $number_open_purchase_orders Number purchase orders (except creating, settled)
 * @property int $number_purchase_orders_state_in_process
 * @property int $number_purchase_orders_state_submitted
 * @property int $number_purchase_orders_state_confirmed
 * @property int $number_purchase_orders_state_settled
 * @property int $number_purchase_orders_state_cancelled
 * @property int $number_purchase_orders_state_not_received
 * @property int $number_purchase_orders_delivery_status_processing
 * @property int $number_purchase_orders_delivery_status_confirmed
 * @property int $number_purchase_orders_delivery_status_ready_to_ship
 * @property int $number_purchase_orders_delivery_status_dispatched
 * @property int $number_purchase_orders_delivery_status_received
 * @property int $number_purchase_orders_delivery_status_checked
 * @property int $number_purchase_orders_delivery_status_settled
 * @property int $number_purchase_orders_delivery_status_not_received
 * @property int $number_purchase_orders_delivery_status_settled_cancelled
 * @property int $number_stock_deliveries Number supplier deliveries
 * @property int $number_stock_deliveries_except_cancelled Number supplier deliveries
 * @property int $number_stock_deliveries_state_in_process
 * @property int $number_stock_deliveries_state_dispatched
 * @property int $number_stock_deliveries_state_received
 * @property int $number_stock_deliveries_state_checked
 * @property int $number_stock_deliveries_state_settled
 * @property int $number_stock_deliveries_status_processing
 * @property int $number_stock_deliveries_status_not_received
 * @property int $number_stock_deliveries_status_settled_placed
 * @property int $number_stock_deliveries_status_settled_cancelled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\OrgSupplier $orgSupplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgSupplierStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgSupplierStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgSupplierStats query()
 * @mixin \Eloquent
 */
class OrgSupplierStats extends Model
{
    protected $table = 'org_supplier_stats';

    protected $guarded = [];

    public function orgSupplier(): BelongsTo
    {
        return $this->belongsTo(OrgSupplier::class);
    }
}
