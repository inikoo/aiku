<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Jul 2023 14:24:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Actions\Utils\Abbreviate;
use App\Actions\Utils\ReadableRandomStringGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\ContentBlock
 *
 * @property int $id
 * @property int $web_block_id
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Web\WebBlock $webBlock
 * @method static Builder|ContentBlock newModelQuery()
 * @method static Builder|ContentBlock newQuery()
 * @method static Builder|ContentBlock onlyTrashed()
 * @method static Builder|ContentBlock query()
 * @method static Builder|ContentBlock withTrashed()
 * @method static Builder|ContentBlock withoutTrashed()
 * @mixin \Eloquent
 */
class ContentBlock extends Model
{
    use UsesTenantConnection;
    use SoftDeletes;
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {

                $webBlockSlug = $this->webBlock->slug;
                if ($webBlockSlug != '') {
                    return Abbreviate::run($webBlockSlug);
                }

                return ReadableRandomStringGenerator::run();
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }


    public function webBlock(): BelongsTo
    {
        return $this->belongsTo(WebBlock::class);
    }

    public function webpageVariants(): BelongsToMany
    {
        return $this->belongsToMany(WebpageVariant::class)->using(ContentBlockWebpageVariant::class)
            ->withTimestamps()->withPivot(['position']);
    }

}
