<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tenancy\TenantInventoryStats
 *
 * @property int $id
 * @property int $tenant_id
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
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|TenantInventoryStats newModelQuery()
 * @method static Builder|TenantInventoryStats newQuery()
 * @method static Builder|TenantInventoryStats query()
 * @mixin Eloquent
 */
class TenantInventoryStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'tenant_inventory_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
