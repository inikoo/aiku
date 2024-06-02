<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:16:41 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $service_id
 * @property int $number_historic_assets
 * @property int $number_services_state_in_process
 * @property int $number_services_state_active
 * @property int $number_services_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Service|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceStats query()
 * @mixin \Eloquent
 */
class ServiceStats extends Model
{
    protected $table = 'service_stats';

    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
