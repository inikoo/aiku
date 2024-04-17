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
use App\Models\Traits\HasUniversalSearch;
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
 * App\Models\Fulfilment\Pallet
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
 * @property string|null $notes
 * @property int $number_stored_items
 * @property string|null $received_at
 * @property string|null $booked_in_at
 * @property string|null $settled_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property int|null $proforma_id
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\MovementPallet> $movements
 * @property-read Organisation $organisation
 * @property-read \App\Models\Fulfilment\PalletDelivery|null $palletDelivery
 * @property-read \App\Models\Fulfilment\PalletReturn|null $palletReturn
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItem> $storedItems
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
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

    protected $guarded = [];
    protected $casts   = [
        'data'   => 'array',
        'state'  => PalletStateEnum::class,
        'status' => PalletStatusEnum::class,
        'type'   => PalletTypeEnum::class
    ];

    protected $attributes = [
        'data'  => '{}',
        'notes' => '',
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
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
}
