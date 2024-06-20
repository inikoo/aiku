<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:14:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inventory\WarehouseAreaStats
 *
 * @property int $id
 * @property int $warehouse_area_id
 * @property int $number_locations
 * @property int $number_locations_status_operational
 * @property int $number_locations_status_broken
 * @property int $number_empty_locations
 * @property int $number_locations_no_stock_slots
 * @property int $number_locations_allow_stocks
 * @property int $number_locations_allow_fulfilment
 * @property int $number_locations_allow_dropshipping
 * @property string $stock_value
 * @property int $number_pallets
 * @property int $number_pallets_with_cartons
 * @property int $number_pallets_with_stored_items
 * @property int $number_pallets_type_pallet
 * @property int $number_pallets_type_box
 * @property int $number_pallets_type_oversize
 * @property int $number_pallets_state_in_process
 * @property int $number_pallets_with_cartons_state_in_process
 * @property int $number_pallets_with_stored_items_state_in_process
 * @property int $number_pallets_state_submitted
 * @property int $number_pallets_with_cartons_state_submitted
 * @property int $number_pallets_with_stored_items_state_submitted
 * @property int $number_pallets_state_confirmed
 * @property int $number_pallets_with_cartons_state_confirmed
 * @property int $number_pallets_with_stored_items_state_confirmed
 * @property int $number_pallets_state_received
 * @property int $number_pallets_with_cartons_state_received
 * @property int $number_pallets_with_stored_items_state_received
 * @property int $number_pallets_state_booking_in
 * @property int $number_pallets_with_cartons_state_booking_in
 * @property int $number_pallets_with_stored_items_state_booking_in
 * @property int $number_pallets_state_booked_in
 * @property int $number_pallets_with_cartons_state_booked_in
 * @property int $number_pallets_with_stored_items_state_booked_in
 * @property int $number_pallets_state_not_received
 * @property int $number_pallets_with_cartons_state_not_received
 * @property int $number_pallets_with_stored_items_state_not_received
 * @property int $number_pallets_state_storing
 * @property int $number_pallets_with_cartons_state_storing
 * @property int $number_pallets_with_stored_items_state_storing
 * @property int $number_pallets_state_picking
 * @property int $number_pallets_with_cartons_state_picking
 * @property int $number_pallets_with_stored_items_state_picking
 * @property int $number_pallets_state_picked
 * @property int $number_pallets_with_cartons_state_picked
 * @property int $number_pallets_with_stored_items_state_picked
 * @property int $number_pallets_state_damaged
 * @property int $number_pallets_with_cartons_state_damaged
 * @property int $number_pallets_with_stored_items_state_damaged
 * @property int $number_pallets_state_lost
 * @property int $number_pallets_with_cartons_state_lost
 * @property int $number_pallets_with_stored_items_state_lost
 * @property int $number_pallets_state_other
 * @property int $number_pallets_with_cartons_state_other
 * @property int $number_pallets_with_stored_items_state_other
 * @property int $number_pallets_state_dispatched
 * @property int $number_pallets_with_cartons_state_dispatched
 * @property int $number_pallets_with_stored_items_state_dispatched
 * @property int $number_pallets_status_in_process
 * @property int $number_pallets_with_cartons_status_in_process
 * @property int $number_pallets_with_stored_items_status_in_process
 * @property int $number_pallets_status_receiving
 * @property int $number_pallets_with_cartons_status_receiving
 * @property int $number_pallets_with_stored_items_status_receiving
 * @property int $number_pallets_status_not_received
 * @property int $number_pallets_with_cartons_status_not_received
 * @property int $number_pallets_with_stored_items_status_not_received
 * @property int $number_pallets_status_storing
 * @property int $number_pallets_with_cartons_status_storing
 * @property int $number_pallets_with_stored_items_status_storing
 * @property int $number_pallets_status_returning
 * @property int $number_pallets_with_cartons_status_returning
 * @property int $number_pallets_with_stored_items_status_returning
 * @property int $number_pallets_status_returned
 * @property int $number_pallets_with_cartons_status_returned
 * @property int $number_pallets_with_stored_items_status_returned
 * @property int $number_pallets_status_incident
 * @property int $number_pallets_with_cartons_status_incident
 * @property int $number_pallets_with_stored_items_status_incident
 * @property int $number_stored_items
 * @property int $number_stored_items_type_pallet
 * @property int $number_stored_items_type_box
 * @property int $number_stored_items_type_oversize
 * @property int $number_stored_items_state_in_process
 * @property int $number_stored_items_state_received
 * @property int $number_stored_items_state_booked_in
 * @property int $number_stored_items_state_settled
 * @property int $number_stored_items_status_in_process
 * @property int $number_stored_items_status_storing
 * @property int $number_stored_items_status_damaged
 * @property int $number_stored_items_status_lost
 * @property int $number_stored_items_status_returned
 * @property int $number_pallet_deliveries
 * @property int $number_pallet_deliveries_state_in_process
 * @property int $number_pallet_deliveries_state_submitted
 * @property int $number_pallet_deliveries_state_confirmed
 * @property int $number_pallet_deliveries_state_received
 * @property int $number_pallet_deliveries_state_not_received
 * @property int $number_pallet_deliveries_state_booking_in
 * @property int $number_pallet_deliveries_state_booked_in
 * @property int $number_pallet_returns
 * @property int $number_pallet_returns_state_in_process
 * @property int $number_pallet_returns_state_submitted
 * @property int $number_pallet_returns_state_confirmed
 * @property int $number_pallet_returns_state_picking
 * @property int $number_pallet_returns_state_picked
 * @property int $number_pallet_returns_state_dispatched
 * @property int $number_pallet_returns_state_consolidated
 * @property int $number_pallet_returns_state_cancel
 * @property int $number_recurring_bills
 * @property int $number_recurring_bills_status_current
 * @property int $number_recurring_bills_status_former
 * @property int $number_customers_interest_pallets_storage
 * @property int $number_customers_interest_items_storage
 * @property int $number_customers_interest_dropshipping
 * @property int $number_customers_status_no_rental_agreement
 * @property int $number_customers_status_inactive
 * @property int $number_customers_status_active
 * @property int $number_customers_status_lost
 * @property int $number_customers_with_stored_items
 * @property int $number_customers_with_pallets
 * @property int $number_customers_with_stored_items_state_in_process
 * @property int $number_customers_with_stored_items_state_received
 * @property int $number_customers_with_stored_items_state_booked_in
 * @property int $number_customers_with_stored_items_state_settled
 * @property int $number_customers_with_stored_items_status_in_process
 * @property int $number_customers_with_stored_items_status_storing
 * @property int $number_customers_with_stored_items_status_damaged
 * @property int $number_customers_with_stored_items_status_lost
 * @property int $number_customers_with_stored_items_status_returned
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Inventory\WarehouseArea|null $warehouse
 * @method static Builder|WarehouseAreaStats newModelQuery()
 * @method static Builder|WarehouseAreaStats newQuery()
 * @method static Builder|WarehouseAreaStats query()
 * @mixin Eloquent
 */
class WarehouseAreaStats extends Model
{
    protected $table = 'warehouse_area_stats';

    protected $guarded = [];


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(WarehouseArea::class);
    }
}
