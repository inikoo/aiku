<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 30 Jan 2024 16:48:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SysAdmin\GroupFulfilmentStat
 *
 * @property int $id
 * @property int $group_id
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
 * @property int $number_pallets_state_not_received
 * @property int $number_pallets_state_booked_in
 * @property int $number_pallets_state_settled
 * @property int $number_pallets_status_in_process
 * @property int $number_pallets_status_storing
 * @property int $number_pallets_status_damaged
 * @property int $number_pallets_status_lost
 * @property int $number_pallets_status_returned
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
 * @property int $number_pallet_deliveries_state_booked_in
 * @property int $number_pallet_returns
 * @property int $number_pallet_returns_state_in_process
 * @property int $number_pallet_returns_state_submitted
 * @property int $number_pallet_returns_state_confirmed
 * @property int $number_pallet_returns_state_in_delivery
 * @property int $number_pallet_returns_state_received
 * @property int $number_pallet_returns_state_done
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder|GroupFulfilmentStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupFulfilmentStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupFulfilmentStat query()
 * @mixin \Eloquent
 */
class GroupFulfilmentStat extends Model
{
    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
