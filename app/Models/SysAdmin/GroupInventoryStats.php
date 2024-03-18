<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Mar 2024 11:36:11 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\GroupInventoryStats
 *
 * @property int $id
 * @property int $group_id
 * @property int $number_trade_units
 * @property int $number_warehouses
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
 * @property int $number_empty_locations
 * @property int $number_locations_no_stock_slots
 * @property string $stock_value
 * @property int $number_stock_families
 * @property int $number_stock_families_state_in_process
 * @property int $number_stock_families_state_active
 * @property int $number_stock_families_state_discontinuing
 * @property int $number_stock_families_state_discontinued
 * @property int $number_stocks
 * @property int $number_stocks_state_in_process
 * @property int $number_stocks_state_active
 * @property int $number_stocks_state_discontinuing
 * @property int $number_stocks_state_discontinued
 * @property int $number_stocks_quantity_status_excess
 * @property int $number_stocks_quantity_status_ideal
 * @property int $number_stocks_quantity_status_low
 * @property int $number_stocks_quantity_status_critical
 * @property int $number_stocks_quantity_status_out_of_stock
 * @property int $number_stocks_quantity_status_error
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_deliveries_state_submitted
 * @property int $number_deliveries_state_picker_assigned
 * @property int $number_deliveries_state_picking
 * @property int $number_deliveries_state_picked
 * @property int $number_deliveries_state_packing
 * @property int $number_deliveries_state_packed
 * @property int $number_deliveries_state_finalised
 * @property int $number_deliveries_state_dispatched
 * @property int $number_deliveries_cancelled_at_state_submitted
 * @property int $number_deliveries_cancelled_at_state_picker_assigned
 * @property int $number_deliveries_cancelled_at_state_picking
 * @property int $number_deliveries_cancelled_at_state_picked
 * @property int $number_deliveries_cancelled_at_state_packing
 * @property int $number_deliveries_cancelled_at_state_packed
 * @property int $number_deliveries_cancelled_at_state_finalised
 * @property int $number_deliveries_cancelled_at_state_dispatched
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static Builder|GroupInventoryStats newModelQuery()
 * @method static Builder|GroupInventoryStats newQuery()
 * @method static Builder|GroupInventoryStats query()
 * @mixin Eloquent
 */
class GroupInventoryStats extends Model
{
    protected $table = 'group_inventory_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
