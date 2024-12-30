<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:15:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\Goods\MasterAsset|null $masterAsset
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterAssetStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterAssetStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterAssetStats query()
 * @mixin \Eloquent
 */
class MasterAssetStats extends Model
{
    protected $table = 'master_product_stats';

    protected $guarded = [];

    public function masterAsset(): BelongsTo
    {
        return $this->belongsTo(MasterAsset::class);
    }
}
