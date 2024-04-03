<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PalletDeliveryStats
 *
 * @property int $id
 * @property int $pallet_delivery_id
 * @property int $number_pallets
 * @property int $number_pallets_type_pallet
 * @property int $number_pallets_type_box
 * @property int $number_pallets_type_oversize
 * @property int $number_pallets_state_in_process
 * @property int $number_pallets_state_submitted
 * @property int $number_pallets_state_confirmed
 * @property int $number_pallets_state_received
 * @property int $number_pallets_state_booking_in
 * @property int $number_pallets_state_booked_in
 * @property int $number_pallets_state_not_received
 * @property int $number_pallets_state_storing
 * @property int $number_pallets_state_picking
 * @property int $number_pallets_state_picked
 * @property int $number_pallets_state_damaged
 * @property int $number_pallets_state_lost
 * @property int $number_pallets_state_dispatched
 * @property int $number_pallets_status_receiving
 * @property int $number_pallets_status_not_received
 * @property int $number_pallets_status_storing
 * @property int $number_pallets_status_returning
 * @property int $number_pallets_status_returned
 * @property int $number_pallets_status_incident
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
 * @property int $number_pallet_returns_state_cancel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryStats query()
 * @mixin \Eloquent
 */
class PalletDeliveryStats extends Model
{
    protected $guarded = [];
}
