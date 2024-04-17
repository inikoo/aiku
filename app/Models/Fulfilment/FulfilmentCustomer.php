<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 16:46:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\CRM\Customer;
use App\Models\Helpers\SerialReference;
use App\Models\Market\RentalAgreement;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Fulfilment\FulfilmentCustomer
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property int $customer_id
 * @property int $fulfilment_id
 * @property bool $pallets_storage
 * @property bool $items_storage
 * @property bool $dropshipping
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
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $webhook_access_key
 * @property-read Customer $customer
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletDelivery> $palletDeliveries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletReturn> $palletReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\FulfilmentProforma> $proformas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RentalAgreement> $rentalAgreements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SerialReference> $serialReferences
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItemReturn> $storedItemReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItem> $storedItems
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
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

    protected $guarded = [];
    protected $casts   = [
        'data' => 'array',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
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

    public function proformas(): HasMany
    {
        return $this->hasMany(FulfilmentProforma::class);
    }

    public function storedItemReturns(): HasMany
    {
        return $this->hasMany(StoredItemReturn::class);
    }

    public function serialReferences(): MorphMany
    {
        return $this->morphMany(SerialReference::class, 'container');
    }

    public function rentalAgreements(): HasMany
    {
        return $this->hasMany(RentalAgreement::class);
    }

}
