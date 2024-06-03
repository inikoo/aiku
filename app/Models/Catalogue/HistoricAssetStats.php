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
 * App\Models\Catalogue\HistoricAssetStats
 *
 * @property int $id
 * @property int $historic_asset_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|HistoricAssetStats newModelQuery()
 * @method static Builder|HistoricAssetStats newQuery()
 * @method static Builder|HistoricAssetStats query()
 * @mixin Eloquent
 */
class HistoricAssetStats extends Model
{
    protected $table = 'historic_asset_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricAsset::class);
    }
    */
}