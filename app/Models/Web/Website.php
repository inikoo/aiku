<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Models\Marketing\Shop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\Website
 *
 * @property int $id
 * @property string $slug
 * @property int $shop_id
 * @property string $state
 * @property string $code
 * @property string $domain
 * @property string $name
 * @property array $settings
 * @property array $data
 * @property array $structure
 * @property int|null $current_layout_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $launched_at
 * @property string|null $closed_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Shop $shop
 * @property-read \App\Models\Web\WebsiteStats|null $stats
 * @property-read \App\Models\Web\WebsiteStats|null $webStats
 * @property-read Collection<int, \App\Models\Web\Webpage> $webpages
 * @method static Builder|Website newModelQuery()
 * @method static Builder|Website newQuery()
 * @method static Builder|Website onlyTrashed()
 * @method static Builder|Website query()
 * @method static Builder|Website withTrashed()
 * @method static Builder|Website withoutTrashed()
 * @mixin Eloquent
 */
class Website extends Model
{
    use UsesTenantConnection;
    use HasSlug;
    use SoftDeletes;

    protected $casts = [
        'data'      => 'array',
        'settings'  => 'array',
        'structure' => 'array',
    ];

    protected $attributes = [
        'data'      => '{}',
        'settings'  => '{}',
        'structure' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebsiteStats::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class);
    }

    public function webStats(): HasOne
    {
        return $this->hasOne(WebsiteStats::class);
    }
}
