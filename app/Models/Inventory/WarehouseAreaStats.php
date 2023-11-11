<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:14:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inventory\WarehouseAreaStats
 *
 * @property int $id
 * @property int $warehouse_area_id
 * @property int $number_locations
 * @property int $number_locations_state_operational
 * @property int $number_locations_state_broken
 * @property int $number_empty_locations
 * @property int $number_locations_no_stock_slots
 * @property string $stock_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Inventory\WarehouseArea $warehouse
 * @method static Builder|WarehouseAreaStats newModelQuery()
 * @method static Builder|WarehouseAreaStats newQuery()
 * @method static Builder|WarehouseAreaStats query()
 * @mixin Eloquent
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
