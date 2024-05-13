<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:39:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Catalogue\HistoricOuterableStats
 *
 * @property int $id
 * @property int $historic_outerable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|HistoricOuterableStats newModelQuery()
 * @method static Builder|HistoricOuterableStats newQuery()
 * @method static Builder|HistoricOuterableStats query()
 * @mixin Eloquent
 */
class HistoricOuterableStats extends Model
{
    protected $table = 'historic_outerable_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricOuterable::class);
    }
    */
}
