<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:13:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\HistoricServiceStats
 *
 * @property int $id
 * @property int $historic_service_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\HistoricService $historicService
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats whereHistoricServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricServiceStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HistoricServiceStats extends Model
{
    protected $table = 'historic_service_stats';

    protected $guarded = [];


    public function historicService(): BelongsTo
    {
        return $this->belongsTo(HistoricService::class);
    }
}
