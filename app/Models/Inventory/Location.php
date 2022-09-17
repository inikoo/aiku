<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:15:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Hydrators\HydrateWarehouse;
use App\Actions\Hydrators\HydrateWarehouseArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Inventory\Location
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property string $state
 * @property string $code
 * @property bool $is_empty
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $organisation_source_id
 * @property-read \App\Models\Inventory\LocationStats|null $stats
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @property-read \App\Models\Inventory\WarehouseArea|null $warehouseArea
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static \Illuminate\Database\Query\Builder|Location onlyTrashed()
 * @method static Builder|Location query()
 * @method static Builder|Location whereCode($value)
 * @method static Builder|Location whereCreatedAt($value)
 * @method static Builder|Location whereData($value)
 * @method static Builder|Location whereDeletedAt($value)
 * @method static Builder|Location whereId($value)
 * @method static Builder|Location whereIsEmpty($value)
 * @method static Builder|Location whereOrganisationId($value)
 * @method static Builder|Location whereOrganisationSourceId($value)
 * @method static Builder|Location whereState($value)
 * @method static Builder|Location whereUpdatedAt($value)
 * @method static Builder|Location whereWarehouseAreaId($value)
 * @method static Builder|Location whereWarehouseId($value)
 * @method static \Illuminate\Database\Query\Builder|Location withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Location withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Inventory\Stock[] $stocks
 * @property-read int|null $stocks_count
 */
class Location extends Model
{
    use SoftDeletes;

    protected $casts = [
        'data'       => 'array',
        'audited_at' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

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

}
