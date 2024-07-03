<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 10:56:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\Helpers\SerialReference;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Fulfilment\Fulfilment
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property string $slug
 * @property int $number_warehouses
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\FulfilmentCustomer> $fulfilmentCustomers
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RecurringBill> $recurringBills
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Rental> $rentals
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SerialReference> $serialReferences
 * @property-read Shop $shop
 * @property-read \App\Models\Fulfilment\FulfilmentStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Warehouse> $warehouses
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfilment withoutTrashed()
 * @mixin \Eloquent
 */
class Fulfilment extends Model
{
    use SoftDeletes;
    use HasSlug;
    use InShop;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->shop->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(6);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(FulfilmentStats::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class)->withTimestamps();
    }

    public function serialReferences(): MorphMany
    {
        return $this->morphMany(SerialReference::class, 'container');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }

    public function fulfilmentCustomers(): HasMany
    {
        return $this->hasMany(FulfilmentCustomer::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(PalletDelivery::class);
    }


}
