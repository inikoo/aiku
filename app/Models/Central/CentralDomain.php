<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:15:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\CentralDomain
 *
 * @property-read \App\Models\Central\CentralDomainStats|null $stats
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static Builder|CentralDomain newModelQuery()
 * @method static Builder|CentralDomain newQuery()
 * @method static Builder|CentralDomain query()
 * @mixin \Eloquent
 */
class CentralDomain extends Model
{
    use HasSlug;
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('slug')
            ->saveSlugsTo('slug');
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(CentralDomainStats::class);
    }

    protected $guarded = [];

}
