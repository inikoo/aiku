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

/**
 * App\Models\Web\WebpageVariantStats
 *
 * @property-read \App\Models\Web\WebpageVariant|null $webpageVariant
 * @method static Builder|WebpageVariantStats newModelQuery()
 * @method static Builder|WebpageVariantStats newQuery()
 * @method static Builder|WebpageVariantStats query()
 * @mixin Eloquent
 */
class WebpageVariantStats extends Model
{
    protected $table = 'webpage_variant_stats';

    protected $guarded = [];

    public function webpageVariant(): BelongsTo
    {
        return $this->belongsTo(WebpageVariant::class);
    }
}
