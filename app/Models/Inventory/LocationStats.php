<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:17:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\LocationStats
 *
 * @property int $id
 * @property int $location_id
 * @property int $number_stock_slots
 * @property int $number_empty_stock_slots
 * @property string $stock_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Location $location
 * @method static Builder|LocationStats newModelQuery()
 * @method static Builder|LocationStats newQuery()
 * @method static Builder|LocationStats query()
 * @method static Builder|LocationStats whereCreatedAt($value)
 * @method static Builder|LocationStats whereId($value)
 * @method static Builder|LocationStats whereLocationId($value)
 * @method static Builder|LocationStats whereNumberEmptyStockSlots($value)
 * @method static Builder|LocationStats whereNumberStockSlots($value)
 * @method static Builder|LocationStats whereStockValue($value)
 * @method static Builder|LocationStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LocationStats extends Model
{
    protected $table = 'location_stats';

    protected $guarded = [];


    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
