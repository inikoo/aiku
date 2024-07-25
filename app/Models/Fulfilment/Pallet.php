<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 15:20:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasRetinaSearch;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InFulfilmentCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string|null $slug
 * @property string|null $reference
 * @property string|null $customer_reference
 * @property int $fulfilment_id
 * @property int $fulfilment_customer_id
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property int|null $rental_id
 * @property int|null $location_id
 * @property int|null $pallet_delivery_id
 * @property int|null $pallet_return_id
 * @property PalletStatusEnum $status
 * @property PalletStateEnum $state
 * @property PalletTypeEnum $type
 * @property int|null $current_recurring_bill_id
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property \Illuminate\Support\Carbon|null $booking_in_at
 * @property \Illuminate\Support\Carbon|null $set_as_not_received_at
 * @property \Illuminate\Support\Carbon|null $booked_in_at
 * @property \Illuminate\Support\Carbon|null $storing_at
 * @property \Illuminate\Support\Carbon|null $requested_for_return_at
 * @property \Illuminate\Support\Carbon|null $picking_at
 * @property \Illuminate\Support\Carbon|null $picked_at
 * @property \Illuminate\Support\Carbon|null $set_as_incident_at
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property string|null $notes
 * @property array $data
 * @property array $incident_report
 * @property bool $with_stored_items
 * @property int $number_stored_item_audits
 * @property int $number_stored_item_audits_state_in_process
 * @property int $number_stored_item_audits_state_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read \App\Models\Fulfilment\RecurringBill|null $currentRecurringBill
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\MovementPallet> $movements
 * @property-read Organisation $organisation
 * @property-read \App\Models\Fulfilment\PalletDelivery|null $palletDelivery
 * @property-read \App\Models\Fulfilment\PalletReturn|null $palletReturn
 * @property-read \App\Models\Fulfilment\Rental|null $rental
 * @property-read \App\Models\Helpers\RetinaSearch|null $retinaSearch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItem> $storedItems
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Warehouse $warehouse
 * @method static \Database\Factories\Fulfilment\PalletFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet locationId($located)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallet withoutTrashed()
 * @mixin \Eloquent
 */
class Pallet extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasFactory;
    use HasUniversalSearch;
    use HasRetinaSearch;
    use InFulfilmentCustomer;

    protected $guarded = [];
    protected $casts   = [
        'data'                    => 'array',
        'incident_report'         => 'array',
        'state'                   => PalletStateEnum::class,
        'status'                  => PalletStatusEnum::class,
        'type'                    => PalletTypeEnum::class,
        'set_as_not_received_at'  => 'datetime',
        'received_at'             => 'datetime',
        'booking_in_at'           => 'datetime',
        'booked_in_at'            => 'datetime',
        'storing_at'              => 'datetime',
        'requested_for_return_at' => 'datetime',
        'picking_at'              => 'datetime',
        'picked_at'               => 'datetime',
        'set_as_incident_at'      => 'datetime',
        'dispatched_at'           => 'datetime',

    ];

    protected $attributes = [
        'data'            => '{}',
        'incident_report' => '{}',
        'notes'           => '',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->doNotGenerateSlugsOnCreate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function scopeLocationId(Builder $query, $located): Builder
    {
        if ($located) {
            return $query->whereNotNull('location_id');
        }

        return $query->whereNull('location_id');
    }



    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MovementPallet::class);
    }

    public function storedItems(): BelongsToMany
    {
        return $this->belongsToMany(StoredItem::class, 'pallet_stored_items')->withPivot('quantity');
    }

    public function palletDelivery(): BelongsTo
    {
        return $this->belongsTo(PalletDelivery::class);
    }

    public function palletReturn(): BelongsTo
    {
        return $this->belongsTo(PalletReturn::class);
    }

    public function currentRecurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class, 'current_recurring_bill_id');
    }

}
