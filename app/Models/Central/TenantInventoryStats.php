<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:25:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\TenantInventoryStats
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $number_warehouses
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
 * @property int $number_empty_locations
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
 * @property int $number_stocks_quantity_status_surplus
 * @property int $number_stocks_quantity_status_optimal
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|TenantInventoryStats newModelQuery()
 * @method static Builder|TenantInventoryStats newQuery()
 * @method static Builder|TenantInventoryStats query()
 * @method static Builder|TenantInventoryStats whereCreatedAt($value)
 * @method static Builder|TenantInventoryStats whereId($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveries($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStateDispatched($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStateFinalised($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStatePacked($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStatePacking($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStatePicked($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStatePickerAssigned($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStatePicking($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesCancelledAtStateSubmitted($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStateDispatched($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStateFinalised($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStatePacked($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStatePacking($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStatePicked($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStatePickerAssigned($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStatePicking($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesStateSubmitted($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesTypeOrder($value)
 * @method static Builder|TenantInventoryStats whereNumberDeliveriesTypeReplacement($value)
 * @method static Builder|TenantInventoryStats whereNumberEmptyLocations($value)
 * @method static Builder|TenantInventoryStats whereNumberLocations($value)
 * @method static Builder|TenantInventoryStats whereNumberLocationsStateBroken($value)
 * @method static Builder|TenantInventoryStats whereNumberLocationsStateOperational($value)
 * @method static Builder|TenantInventoryStats whereNumberStockFamilies($value)
 * @method static Builder|TenantInventoryStats whereNumberStockFamiliesStateActive($value)
 * @method static Builder|TenantInventoryStats whereNumberStockFamiliesStateDiscontinued($value)
 * @method static Builder|TenantInventoryStats whereNumberStockFamiliesStateDiscontinuing($value)
 * @method static Builder|TenantInventoryStats whereNumberStockFamiliesStateInProcess($value)
 * @method static Builder|TenantInventoryStats whereNumberStocks($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusCritical($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusError($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusLow($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusOptimal($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusOutOfStock($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksQuantityStatusSurplus($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksStateActive($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksStateDiscontinued($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksStateDiscontinuing($value)
 * @method static Builder|TenantInventoryStats whereNumberStocksStateInProcess($value)
 * @method static Builder|TenantInventoryStats whereNumberWarehouseAreas($value)
 * @method static Builder|TenantInventoryStats whereNumberWarehouses($value)
 * @method static Builder|TenantInventoryStats whereTenantId($value)
 * @method static Builder|TenantInventoryStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TenantInventoryStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_inventory_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
