<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\OrganisationInventoryStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_warehouses
 * @property int $number_warehouses_state_in_process
 * @property int $number_warehouses_state_open
 * @property int $number_warehouses_state_closing_down
 * @property int $number_warehouses_state_closed
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_status_operational
 * @property int $number_locations_status_broken
 * @property int $number_empty_locations
 * @property int $number_locations_no_stock_slots
 * @property int $number_locations_allow_stocks
 * @property int $number_locations_allow_fulfilment
 * @property int $number_locations_allow_dropshipping
 * @property string $stock_value
 * @property int $number_stock_families
 * @property int $number_org_stock_families_state_in_process
 * @property int $number_org_stock_families_state_active
 * @property int $number_org_stock_families_state_discontinuing
 * @property int $number_org_stock_families_state_discontinued
 * @property int $number_stocks
 * @property int $number_org_stocks_state_active
 * @property int $number_org_stocks_state_discontinuing
 * @property int $number_org_stocks_state_discontinued
 * @property int $number_org_stocks_state_suspended
 * @property int $number_org_stocks_quantity_status_excess
 * @property int $number_org_stocks_quantity_status_ideal
 * @property int $number_org_stocks_quantity_status_low
 * @property int $number_org_stocks_quantity_status_critical
 * @property int $number_org_stocks_quantity_status_out_of_stock
 * @property int $number_org_stocks_quantity_status_error
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_deliveries_state_on_hold
 * @property int $number_deliveries_state_picking
 * @property int $number_deliveries_state_picked
 * @property int $number_deliveries_state_packing
 * @property int $number_deliveries_state_packed
 * @property int $number_deliveries_state_finalised
 * @property int $number_deliveries_state_settled
 * @property int $number_deliveries_cancelled_at_state_on_hold
 * @property int $number_deliveries_cancelled_at_state_picking
 * @property int $number_deliveries_cancelled_at_state_picked
 * @property int $number_deliveries_cancelled_at_state_packing
 * @property int $number_deliveries_cancelled_at_state_packed
 * @property int $number_deliveries_cancelled_at_state_finalised
 * @property int $number_deliveries_cancelled_at_state_settled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @method static Builder|OrganisationInventoryStats newModelQuery()
 * @method static Builder|OrganisationInventoryStats newQuery()
 * @method static Builder|OrganisationInventoryStats query()
 * @mixin Eloquent
 */
class OrganisationInventoryStats extends Model
{
    protected $table = 'organisation_inventory_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
