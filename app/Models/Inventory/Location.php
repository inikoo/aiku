<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:15:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Actions\Inventory\WarehouseArea\HydrateWarehouseArea;
use App\Enums\Inventory\Location\LocationStatusEnum;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\Location
 *
 * @property int $id
 * @property string $slug
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property LocationStatusEnum $status
 * @property string $code
 * @property string $unit_quantity
 * @property string $value
 * @property bool $is_empty
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $audited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Inventory\LocationStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Stock> $stocks
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @property-read \App\Models\Inventory\WarehouseArea|null $warehouseArea
 * @method static \Database\Factories\Inventory\LocationFactory factory($count = null, $state = [])
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location onlyTrashed()
 * @method static Builder|Location query()
 * @method static Builder|Location withTrashed()
 * @method static Builder|Location withoutTrashed()
 * @mixin \Eloquent
 */
class Location extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'       => 'array',
        'audited_at' => 'datetime',
        'status'     => LocationStatusEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    protected static function booted()
    {
        static::created(
            function (Location $location) {
                HydrateWarehouse::make()->locations($location->warehouse);
                if ($location->warehouse_area_id) {
                    HydrateWarehouseArea::make()->locations($location->warehouseArea);
                }
            }
        );
        static::deleted(
            function (Location $location) {
                HydrateWarehouse::make()->locations($location->warehouse);
                if ($location->warehouse_area_id) {
                    HydrateWarehouseArea::make()->locations($location->warehouseArea);
                }
            }
        );

        static::updated(function (Location $location) {
            if (!$location->wasRecentlyCreated) {
                if ($location->wasChanged('warehouse_area_id')) {
                    HydrateWarehouseArea::make()->locations($location->warehouseArea);
                }
                if ($location->wasChanged('warehouse_id')) {
                    HydrateWarehouse::make()->locations($location->warehouse);
                }
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseArea(): BelongsTo
    {
        return $this->belongsTo(WarehouseArea::class);
    }


    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class)->using(LocationStock::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(LocationStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
