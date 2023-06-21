<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 12:07:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\AgentStats
 *
 * @property int $id
 * @property int $agent_id
 * @property int $number_suppliers Active suppliers, status=true
 * @property int $number_archived_suppliers Archived suppliers status=false
 * @property int $number_suppliers_type_supplier Active suppliers. status=true,type=supplier
 * @property int $number_archived_suppliers_type_supplier Archived suppliers. status=false,type=supplier
 * @property int $number_suppliers_type_sub_supplier Active suppliers. status=true,type=sub-supplier
 * @property int $number_archived_suppliers_type_sub_supplier Archived suppliers. status=false,type=sub-supplier
 * @property int $number_supplier_products Number supplier products (all excluding discontinued)
 * @property int $number_supplier_deliveries Number supplier deliveries (all excluding discontinued)
 * @property int $supplier_products_count Number supplier products
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
 * @property-read \App\Models\Procurement\Agent $agent
 * @method static Builder|AgentStats newModelQuery()
 * @method static Builder|AgentStats newQuery()
 * @method static Builder|AgentStats query()
 * @mixin Eloquent
 */
class AgentStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'agent_stats';

    protected $guarded = [];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
