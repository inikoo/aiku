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
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Inventory\LocationStats
 *
 * @property int $id
 * @property int $location_id
 * @property int $number_stock_slots
 * @property int $number_empty_stock_slots
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Location $location
 * @method static Builder|LocationStats newModelQuery()
 * @method static Builder|LocationStats newQuery()
 * @method static Builder|LocationStats query()
 * @mixin \Eloquent
 */
class LocationStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'location_stats';

    protected $guarded = [];


    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
