<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\OrganisationProcurementStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_org_agents Total number agens active+archived
 * @property int $number_active_org_agents Active agents, status=true
 * @property int $number_archived_org_agents Archived agents, status=false
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
 * @property int $number_available_org_supplier_products
 * @property int $number_no_available_org_supplier_products only for state=active|discontinuing
 * @property int $number_org_supplier_products_state_active
 * @property int $number_org_supplier_products_state_discontinuing
 * @property int $number_org_supplier_products_state_discontinued
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @method static Builder|OrganisationProcurementStats newModelQuery()
 * @method static Builder|OrganisationProcurementStats newQuery()
 * @method static Builder|OrganisationProcurementStats query()
 * @mixin Eloquent
 */
class OrganisationProcurementStats extends Model
{
    protected $table = 'organisation_procurement_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
