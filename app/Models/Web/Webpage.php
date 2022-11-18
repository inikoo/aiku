<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:12 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\Webpage
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $type
 * @property int $webnode_id
 * @property array $components
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\WebpageStats|null $stats
 * @property-read \App\Models\Web\Webnode $webnode
 * @method static Builder|Webpage newModelQuery()
 * @method static Builder|Webpage newQuery()
 * @method static Builder|Webpage query()
 * @method static Builder|Webpage whereCode($value)
 * @method static Builder|Webpage whereComponents($value)
 * @method static Builder|Webpage whereCreatedAt($value)
 * @method static Builder|Webpage whereId($value)
 * @method static Builder|Webpage whereSlug($value)
 * @method static Builder|Webpage whereType($value)
 * @method static Builder|Webpage whereUpdatedAt($value)
 * @method static Builder|Webpage whereWebnodeId($value)
 * @mixin \Eloquent
 */
class Webpage extends Model
{
    use HasSlug;

    protected $casts = [
        'components' => 'array',
    ];

    protected $attributes = [
        'components' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }


    public function webnode(): BelongsTo
    {
        return $this->belongsTo(Webnode::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebpageStats::class);
    }
}
