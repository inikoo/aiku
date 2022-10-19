<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


/**
 * App\Models\Web\Webnode
 *
 * @property int $id
 * @property string $slug
 * @property string $type
 * @property string|null $locus for structural type, identification od the node
 * @property int $website_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $main_webpage_id
 * @property-read \App\Models\Web\Webpage|null $mainWebpage
 * @property-read \App\Models\Web\WebnodeStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Web\Webpage[] $webpages
 * @property-read int|null $webpages_count
 * @property-read \App\Models\Web\Website $website
 * @method static Builder|Webnode newModelQuery()
 * @method static Builder|Webnode newQuery()
 * @method static Builder|Webnode query()
 * @method static Builder|Webnode whereCreatedAt($value)
 * @method static Builder|Webnode whereId($value)
 * @method static Builder|Webnode whereLocus($value)
 * @method static Builder|Webnode whereMainWebpageId($value)
 * @method static Builder|Webnode whereSlug($value)
 * @method static Builder|Webnode whereType($value)
 * @method static Builder|Webnode whereUpdatedAt($value)
 * @method static Builder|Webnode whereWebsiteId($value)
 * @mixin \Eloquent
 */
class Webnode extends Model
{
    use HasSlug;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('slug')
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebnodeStats::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function mainWebpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class, 'main_webpage_id');
    }

    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class);
    }

}
