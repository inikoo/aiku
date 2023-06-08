<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:12 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\WebpageVariant
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property int $webpage_id
 * @property array $components
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WebpageStats|null $stats
 * @property-read Webpage $webpage
 * @method static Builder|WebpageVariant newModelQuery()
 * @method static Builder|WebpageVariant newQuery()
 * @method static Builder|WebpageVariant query()
 * @mixin Eloquent
 */
class WebpageVariant extends Model
{
    use UsesTenantConnection;
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
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }


    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebpageStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
