<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 10:53:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Web\WebnodeStats
 *
 * @property int $id
 * @property int $webnode_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\Webnode $webnode
 * @method static Builder|WebnodeStats newModelQuery()
 * @method static Builder|WebnodeStats newQuery()
 * @method static Builder|WebnodeStats query()
 * @mixin \Eloquent
 */
class WebnodeStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'webnode_stats';

    protected $guarded = [];

    public function webnode(): BelongsTo
    {
        return $this->belongsTo(Webnode::class);
    }
}
