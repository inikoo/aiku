<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:39:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\HistoricProductStats
 *
 * @property int $id
 * @property int $historic_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\HistoricProduct $historicProduct
 * @method static Builder|HistoricProductStats newModelQuery()
 * @method static Builder|HistoricProductStats newQuery()
 * @method static Builder|HistoricProductStats query()
 * @method static Builder|HistoricProductStats whereCreatedAt($value)
 * @method static Builder|HistoricProductStats whereHistoricProductId($value)
 * @method static Builder|HistoricProductStats whereId($value)
 * @method static Builder|HistoricProductStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HistoricProductStats extends Model
{
    protected $table = 'historic_product_stats';

    protected $guarded = [];


    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricProduct::class);
    }
}
