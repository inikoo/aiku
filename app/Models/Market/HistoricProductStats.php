<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:39:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Market\HistoricProductStats
 *
 * @property int $id
 * @property int $historic_product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|HistoricProductStats newModelQuery()
 * @method static Builder|HistoricProductStats newQuery()
 * @method static Builder|HistoricProductStats query()
 * @mixin Eloquent
 */
class HistoricProductStats extends Model
{
    protected $table = 'historic_product_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricProduct::class);
    }
    */
}
