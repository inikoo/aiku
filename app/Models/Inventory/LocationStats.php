<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:17:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inventory\LocationStats
 *
 * @property int $id
 * @property int $location_id
 * @property int $number_org_stock_slots
 * @property int $number_empty_stock_slots
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Location $location
 * @method static Builder|LocationStats newModelQuery()
 * @method static Builder|LocationStats newQuery()
 * @method static Builder|LocationStats query()
 * @mixin Eloquent
 */
class LocationStats extends Model
{
    protected $table = 'location_stats';

    protected $guarded = [];


    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
