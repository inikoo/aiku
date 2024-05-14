<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 11:02:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fulfilment\FulfilmentStats
 *
 * @property int $id
 * @property int $fulfilment_id
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
 * @property int $number_recurring_bills
 * @property int $number_recurring_bills_status_current
 * @property int $number_recurring_bills_status_former
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentStats query()
 * @mixin \Eloquent
 */
class FulfilmentStats extends Model
{
    protected $table = 'fulfilment_stats';

    protected $guarded = [];

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }
}
