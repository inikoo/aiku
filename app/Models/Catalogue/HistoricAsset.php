<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 21:48:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Catalogue\HistoricAsset
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property bool $status
 * @property int $asset_id
 * @property string $model_type
 * @property int $model_id
 * @property string $price unit price
 * @property string|null $code
 * @property string|null $name
 * @property string|null $units units in outer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\Catalogue\Asset|null $product
 * @property-read \App\Models\Catalogue\HistoricAssetStats|null $stats
 * @method static Builder|HistoricAsset newModelQuery()
 * @method static Builder|HistoricAsset newQuery()
 * @method static Builder|HistoricAsset onlyTrashed()
 * @method static Builder|HistoricAsset query()
 * @method static Builder|HistoricAsset withTrashed()
 * @method static Builder|HistoricAsset withoutTrashed()
 * @mixin Eloquent
 */
class HistoricAsset extends Model
{
    use SoftDeletes;

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(HistoricAssetStats::class);
    }
}