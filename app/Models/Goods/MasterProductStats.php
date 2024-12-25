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
 * @property int $id
 * @property int $master_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Goods\MasterProduct $masterProduct
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterProductStats query()
 * @mixin \Eloquent
 */
class MasterProductStats extends Model
{
    protected $table = 'master_product_stats';

    protected $guarded = [];

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }
}
