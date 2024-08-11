<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:11:39 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrgAgentStats
 *
 * @property int $id
 * @property int $org_agent_id
 * @property int $number_org_suppliers Active + Archived  suppliers
 * @property int $number_active_org_suppliers Active suppliers, status=true
 * @property int $number_archived_org_suppliers Archived suppliers status=false
 * @property int $number_independent_org_suppliers Active + Archived no agent suppliers
 * @property int $number_active_independent_org_suppliers Active no agent suppliers, status=true
 * @property int $number_archived_independent_org_suppliers Archived no agent suppliers status=false
 * @property int $number_org_suppliers_in_agents Active + Archived suppliers
 * @property int $number_active_org_suppliers_in_agents Active suppliers, status=true
 * @property int $number_archived_org_suppliers_in_agents Archived suppliers status=false
 * @property int $number_org_supplier_products
 * @property int $number_current_org_supplier_products status=true
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
 * @property int $number_stock_deliveries Number supplier deliveries
 * @property int $number_stock_deliveries_except_cancelled Number supplier deliveries
 * @property int $number_stock_deliveries_state_creating
 * @property int $number_stock_deliveries_state_dispatched
 * @property int $number_stock_deliveries_state_received
 * @property int $number_stock_deliveries_state_checked
 * @property int $number_stock_deliveries_state_settled
 * @property int $number_stock_deliveries_status_processing
 * @property int $number_stock_deliveries_status_settled_placed
 * @property int $number_stock_deliveries_status_settled_cancelled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\OrgAgent $orgAgent
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgentStats query()
 * @mixin \Eloquent
 */
class OrgAgentStats extends Model
{
    protected $table = 'org_agent_stats';

    protected $guarded = [];

    public function orgAgent(): BelongsTo
    {
        return $this->belongsTo(OrgAgent::class);
    }
}
