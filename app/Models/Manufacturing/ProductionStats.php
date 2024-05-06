<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Manufacturing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $production_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Manufacturing\Production $production
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionStats query()
 * @mixin \Eloquent
 */
class ProductionStats extends Model
{
    protected $table = 'production_stats';

    protected $guarded = [];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }
}
