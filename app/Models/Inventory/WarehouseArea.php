<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:13:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\WarehouseArea
 *
 * @property int $id
 * @property string $slug
 * @property int $warehouse_id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read \App\Models\Inventory\WarehouseAreaStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea query()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WarehouseArea withoutTrashed()
 * @mixin \Eloquent
 */
class WarehouseArea extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(16);
    }


    protected static function booted()
    {
        static::created(
            function (WarehouseArea $warehouseArea) {
                HydrateWarehouse::make()->warehouseAreas($warehouseArea->warehouse);
            }
        );
        static::deleted(
            function (WarehouseArea $warehouseArea) {
                HydrateWarehouse::make()->warehouseAreas($warehouseArea->warehouse);
            }
        );

        static::updated(function (WarehouseArea $warehouseArea) {
            if (!$warehouseArea->wasRecentlyCreated) {
                if ($warehouseArea->wasChanged('warehouse_id')) {
                    HydrateWarehouse::make()->warehouseAreas($warehouseArea->warehouse);
                }
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WarehouseAreaStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
