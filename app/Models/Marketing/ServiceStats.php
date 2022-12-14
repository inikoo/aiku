<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:10:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\ServiceStats
 *
 * @property int $id
 * @property int $service_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceStats extends Model
{
    protected $table = 'service_stats';

    protected $guarded = [];


    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
