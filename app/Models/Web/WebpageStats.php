<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 10:53:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Web\WebpageStats
 *
 * @property int $id
 * @property int $webpage_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WebpageVariant $webpage
 * @method static Builder|WebpageStats newModelQuery()
 * @method static Builder|WebpageStats newQuery()
 * @method static Builder|WebpageStats query()
 * @mixin Eloquent
 */
class WebpageStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'webpage_stats';

    protected $guarded = [];

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(WebpageVariant::class);
    }
}
