<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 May 2023 17:09:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\GroupSupplyChainStats
 *
 * @property int $id
 * @property int $group_id
 * @property int $number_agents Total number agens active+archived
 * @property int $number_active_agents Active agents, status=true
 * @property int $number_archived_agents Archived agents, status=false
 * @property int $number_suppliers Active + Archived  suppliers
 * @property int $number_active_suppliers Active suppliers, status=true
 * @property int $number_archived_suppliers Archived suppliers status=false
 * @property int $number_suppliers_in_agents Active + Archived suppliers
 * @property int $number_active_suppliers_in_agents Active suppliers, status=true
 * @property int $number_archived_suppliers_in_agents Archived suppliers status=false
 * @property int $number_supplier_products
 * @property int $number_supplier_products_state_active_and_discontinuing
 * @property int $number_supplier_products_state_creating
 * @property int $number_supplier_products_state_active
 * @property int $number_supplier_products_state_discontinuing
 * @property int $number_supplier_products_state_discontinued
 * @property int $number_supplier_products_stock_quantity_status_excess
 * @property int $number_supplier_products_stock_quantity_status_ideal
 * @property int $number_supplier_products_stock_quantity_status_low
 * @property int $number_supplier_products_stock_quantity_status_critical
 * @property int $number_supplier_products_stock_quantity_status_out_of_stock
 * @property int $number_supplier_products_stock_quantity_status_no_applicable
 * @property int $number_purchase_orders
 * @property int $number_purchase_orders_except_cancelled Number purchase orders (except cancelled and failed)
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
 * @property int $number_supplier_deliveries Number supplier deliveries
 * @property int $number_supplier_deliveries_except_cancelled Number supplier deliveries
 * @property int $number_supplier_deliveries_state_creating
 * @property int $number_supplier_deliveries_state_dispatched
 * @property int $number_supplier_deliveries_state_received
 * @property int $number_supplier_deliveries_state_checked
 * @property int $number_supplier_deliveries_state_settled
 * @property int $number_supplier_deliveries_status_processing
 * @property int $number_supplier_deliveries_status_settled_placed
 * @property int $number_supplier_deliveries_status_settled_cancelled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static Builder|GroupSupplyChainStats newModelQuery()
 * @method static Builder|GroupSupplyChainStats newQuery()
 * @method static Builder|GroupSupplyChainStats query()
 * @mixin Eloquent
 */
class GroupSupplyChainStats extends Model
{
    protected $table = 'group_supply_chain_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
