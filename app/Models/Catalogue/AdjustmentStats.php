<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 21:13:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $adjustment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Adjustment $adjustment
 * @method static \Illuminate\Database\Eloquent\Builder|AdjustmentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdjustmentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdjustmentStats query()
 * @mixin \Eloquent
 */
class AdjustmentStats extends Model
{
    protected $table = 'adjustment_stats';

    protected $guarded = [];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(Adjustment::class);
    }
}
