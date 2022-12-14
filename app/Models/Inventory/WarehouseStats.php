<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:12:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Central\Tenant\HydrateTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\WarehouseStats
 *
 * @property int $id
 * @property int $warehouse_id
 * @property int $number_warehouse_areas
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
 * @property int $number_empty_locations
 * @property string $stock_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @method static Builder|WarehouseStats newModelQuery()
 * @method static Builder|WarehouseStats newQuery()
 * @method static Builder|WarehouseStats query()
 * @method static Builder|WarehouseStats whereCreatedAt($value)
 * @method static Builder|WarehouseStats whereId($value)
 * @method static Builder|WarehouseStats whereNumberEmptyLocations($value)
 * @method static Builder|WarehouseStats whereNumberLocations($value)
 * @method static Builder|WarehouseStats whereNumberLocationsStateBroken($value)
 * @method static Builder|WarehouseStats whereNumberLocationsStateOperational($value)
 * @method static Builder|WarehouseStats whereNumberWarehouseAreas($value)
 * @method static Builder|WarehouseStats whereStockValue($value)
 * @method static Builder|WarehouseStats whereUpdatedAt($value)
 * @method static Builder|WarehouseStats whereWarehouseId($value)
 * @mixin \Eloquent
 */
class WarehouseStats extends Model
{
    protected $table = 'warehouse_stats';

    protected $guarded = [];

    protected static function booted()
    {
        static::updated(function (WarehouseStats $warehouseStats) {
            if (!$warehouseStats->wasRecentlyCreated) {
                HydrateTenant::make()->warehouseStats();
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
