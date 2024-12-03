<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Mar 2024 11:36:11 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
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
 * @property string $stock_commercial_value
 * @property int $number_org_stock_audits
 * @property int $number_org_stock_audits_state_in_process
 * @property int $number_org_stock_audits_state_completed
 * @property int $number_org_stock_audit_deltas
 * @property int $number_org_stock_audit_delta_type_addition
 * @property int $number_org_stock_audit_delta_type_subtraction
 * @property int $number_org_stock_audit_delta_type_no_change
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInventoryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInventoryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInventoryStats query()
 * @mixin \Eloquent
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
