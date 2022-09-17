<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 22:55:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Organisations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Organisations\OrganisationInventoryStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_warehouses
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
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
 * @property-read \App\Models\Organisations\Organisation|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStateApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStateCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStateCancelledToRestock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStateDispatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePacked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePackedDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePickerAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStatePicking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesStateReadyToBePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesTypeOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberDeliveriesTypeReplacement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberLocations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberLocationsStateBroken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberLocationsStateOperational($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusCritical($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusOptimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusOutOfStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksQuantityStatusSurplus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksStateDiscontinued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksStateDiscontinuing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberStocksStateInProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberWarehouseAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberWarehouses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $number_empty_locations
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationInventoryStats whereNumberEmptyLocations($value)
 */
class OrganisationInventoryStats extends Model
{
    protected $table = 'organisation_inventory_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

}
