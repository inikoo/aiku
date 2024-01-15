<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\Webpage
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $url
 * @property int $level
 * @property bool $is_fixed
 * @property string $state
 * @property string $type
 * @property string $purpose
 * @property int|null $parent_id
 * @property int $website_id
 * @property int|null $unpublished_snapshot_id
 * @property int|null $live_snapshot_id
 * @property mixed $compiled_layout
 * @property string|null $ready_at
 * @property string|null $live_at
 * @property string|null $closed_at
 * @property string|null $published_checksum
 * @property bool $is_dirty
 * @property mixed $data
 * @property mixed $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read \App\Models\Web\WebpageVariant|null $mainVariant
 * @property-read \App\Models\Web\WebpageStats|null $stats
 * @property-read Collection<int, \App\Models\Web\WebpageVariant> $variants
 * @property-read \App\Models\Web\Website $website
 * @method static Builder|Webpage newModelQuery()
 * @method static Builder|Webpage newQuery()
 * @method static Builder|Webpage query()
 * @mixin Eloquent
 */
class Webpage extends Model
{
    use HasSlug;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WebpageStats::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function mainVariant(): BelongsTo
    {
        return $this->belongsTo(WebpageVariant::class, 'main_variant_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(WebpageVariant::class);
    }
}
