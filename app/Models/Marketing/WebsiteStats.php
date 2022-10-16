<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:41:47 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\WebsiteStats
 *
 * @property int $id
 * @property int $website_id
 * @property int $number_webpages
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Website $website
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats whereNumberWebpages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteStats whereWebsiteId($value)
 * @mixin \Eloquent
 */
class WebsiteStats extends Model
{
    protected $table = 'website_stats';

    protected $guarded = [];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
