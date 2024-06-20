<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 16:46:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatus;
use App\Models\CRM\Customer;
use App\Models\Helpers\SerialReference;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InFulfilment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property FulfilmentCustomerStatus $status
 * @property string $slug
 * @property int $customer_id
 * @property int $fulfilment_id
 * @property bool $pallets_storage
 * @property bool $items_storage
 * @property bool $dropshipping
 * @property string|null $webhook_access_key
 * @property int|null $current_recurring_bill_id
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
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property-read \App\Models\Fulfilment\RecurringBill|null $currentRecurringBill
 * @property-read Customer $customer
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletDelivery> $palletDeliveries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletReturn> $palletReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RecurringBill> $recurringBills
 * @property-read \App\Models\Fulfilment\RentalAgreement|null $rentalAgreement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RentalAgreementClause> $rentalAgreementClauses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SerialReference> $serialReferences
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItemReturn> $storedItemReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItem> $storedItems
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FulfilmentCustomer withoutTrashed()
 * @mixin \Eloquent
 */
class FulfilmentCustomer extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasSlug;
    use InFulfilment;

    protected $guarded = [];
    protected $casts   = [
        'data'   => 'array',
        'status' => FulfilmentCustomerStatus::class

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->customer->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(16);
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class);
    }

    public function storedItems(): HasMany
    {
        return $this->hasMany(StoredItem::class);
    }

    public function palletDeliveries(): HasMany
    {
        return $this->hasMany(PalletDelivery::class);
    }

    public function palletReturns(): HasMany
    {
        return $this->hasMany(PalletReturn::class);
    }

    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }

    public function storedItemReturns(): HasMany
    {
        return $this->hasMany(StoredItemReturn::class);
    }

    public function serialReferences(): MorphMany
    {
        return $this->morphMany(SerialReference::class, 'container');
    }

    public function rentalAgreement(): HasOne
    {
        return $this->hasOne(RentalAgreement::class);
    }

    public function rentalAgreementClauses(): HasMany
    {
        return $this->hasMany(RentalAgreementClause::class);
    }

    public function currentRecurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class, 'current_recurring_bill_id');
    }

}
