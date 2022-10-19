<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 10:53:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Web\WebpageStats
 *
 * @property int $id
 * @property int $webpage_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\Webpage $webpage
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageStats whereWebpageId($value)
 * @mixin \Eloquent
 */
class WebpageStats extends Model
{
    protected $table = 'webpage_stats';

    protected $guarded = [];

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }
}
