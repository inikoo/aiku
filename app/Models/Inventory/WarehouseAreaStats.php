<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:14:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\WarehouseAreaStats
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\WarehouseArea $warehouse
 * @method static Builder|WarehouseAreaStats newModelQuery()
 * @method static Builder|WarehouseAreaStats newQuery()
 * @method static Builder|WarehouseAreaStats query()
 * @method static Builder|WarehouseAreaStats whereCreatedAt($value)
 * @method static Builder|WarehouseAreaStats whereId($value)
 * @method static Builder|WarehouseAreaStats whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $warehouse_area_id
 * @property int $number_locations
 * @property int $number_empty_locations
 * @property string $stock_value
 * @method static Builder|WarehouseAreaStats whereNumberEmptyLocations($value)
 * @method static Builder|WarehouseAreaStats whereNumberLocations($value)
 * @method static Builder|WarehouseAreaStats whereStockValue($value)
 * @method static Builder|WarehouseAreaStats whereWarehouseAreaId($value)
 */
class WarehouseAreaStats extends Model
{
    protected $table = 'warehouse_area_stats';

    protected $guarded = [];


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(WarehouseArea::class);
    }
}
