<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Web\Webnode> $webnodes
 * @property int|null $current_layout_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $launched_at
 * @property string|null $closed_at
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @property-read Shop $shop
 * @property-read \App\Models\Web\WebsiteStats|null $stats
 * @method static Builder|Website newModelQuery()
 * @method static Builder|Website newQuery()
 * @method static Builder|Website query()
 * @mixin \Eloquent
 */
class Website extends Model
{
    use UsesTenantConnection;
    use HasSlug;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'webnodes' => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
        'webnodes' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
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

    public function webnodes(): HasMany
    {
        return $this->hasMany(Webnode::class);
    }
}
