<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:25:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantInventoryStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_warehouses
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
 * @property int $number_empty_locations
 * @property int $number_stocks
 * @property int $number_stocks_state_in_process
 * @property int $number_stocks_state_active
 * @property int $number_stocks_state_discontinuing
 * @property int $number_stocks_state_discontinued
 * @property int $number_stocks_quantity_status_surplus
 * @property int $number_stocks_quantity_status_optimal
 * @property int $number_stocks_quantity_status_low
 * @property int $number_stocks_quantity_status_critical
 * @property int $number_stocks_quantity_status_out_of_stock
 * @property int $number_stocks_quantity_status_error
 * @property int $number_deliveries
 * @property int $number_deliveries_type_order
 * @property int $number_deliveries_type_replacement
 * @property int $number_deliveries_state_ready_to_be_picked
 * @property int $number_deliveries_state_picker_assigned
 * @property int $number_deliveries_state_picking
 * @property int $number_deliveries_state_picked
 * @property int $number_deliveries_state_packing
 * @property int $number_deliveries_state_packed
 * @property int $number_deliveries_state_packed_done
 * @property int $number_deliveries_state_approved
 * @property int $number_deliveries_state_dispatched
 * @property int $number_deliveries_state_cancelled
 * @property int $number_deliveries_state_cancelled_to_restock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStateApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStateCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStateCancelledToRestock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStateDispatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePacked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePackedDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePickerAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStatePicking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesStateReadyToBePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesTypeOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberDeliveriesTypeReplacement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberEmptyLocations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberLocations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberLocationsStateBroken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberLocationsStateOperational($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusCritical($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusOptimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusOutOfStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksQuantityStatusSurplus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksStateDiscontinued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksStateDiscontinuing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberStocksStateInProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberWarehouseAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereNumberWarehouses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantInventoryStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TenantInventoryStats extends Model
{
    protected $table = 'tenant_inventory_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
