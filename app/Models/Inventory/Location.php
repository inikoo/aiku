<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:15:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Actions\Inventory\WarehouseArea\HydrateWarehouseArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\Location
 *
 * @property int $id
 * @property string $slug
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property string $state
 * @property string $code
 * @property bool $is_empty
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Inventory\LocationStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Stock> $stocks
 * @property-read int|null $stocks_count
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @property-read \App\Models\Inventory\WarehouseArea|null $warehouseArea
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location onlyTrashed()
 * @method static Builder|Location query()
 * @method static Builder|Location whereCode($value)
 * @method static Builder|Location whereCreatedAt($value)
 * @method static Builder|Location whereData($value)
 * @method static Builder|Location whereDeletedAt($value)
 * @method static Builder|Location whereId($value)
 * @method static Builder|Location whereIsEmpty($value)
 * @method static Builder|Location whereSlug($value)
 * @method static Builder|Location whereSourceId($value)
 * @method static Builder|Location whereState($value)
 * @method static Builder|Location whereUpdatedAt($value)
 * @method static Builder|Location whereWarehouseAreaId($value)
 * @method static Builder|Location whereWarehouseId($value)
 * @method static Builder|Location withTrashed()
 * @method static Builder|Location withoutTrashed()
 * @mixin \Eloquent
 */
class Location extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data'       => 'array',
        'audited_at' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    protected static function booted()
    {
        static::created(
            function (Location $location) {
                HydrateWarehouse::make()->locations($location->warehouse);
                if($location->warehouse_area_id){
                    HydrateWarehouseArea::make()->locations($location->warehouseArea);
                }

            }
        );
        static::deleted(
            function (Location $location) {
                HydrateWarehouse::make()->locations($location->warehouse);
                if($location->warehouse_area_id){
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
