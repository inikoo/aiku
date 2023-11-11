<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:15:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Market\Shop;
use App\Models\SysAdmin\SysUser;
use App\Models\Organisation\Organisation;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\Domain
 *
 * @property int $id
 * @property string $slug
 * @property int $organisation_id
 * @property int $website_id
 * @property int $shop_id
 * @property string $domain
 * @property string|null $cloudflare_id
 * @property string|null $cloudflare_status
 * @property int|null $iris_id
 * @property string|null $iris_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Shop|null $shop
 * @property-read \App\Models\Central\DomainStats|null $stats
 * @property-read SysUser|null $sysUser
 * @property-read Organisation $organisation
 * @property-read Website|null $website
 * @method static Builder|Domain newModelQuery()
 * @method static Builder|Domain newQuery()
 * @method static Builder|Domain onlyTrashed()
 * @method static Builder|Domain query()
 * @method static Builder|Domain withTrashed()
 * @method static Builder|Domain withoutTrashed()
 * @mixin Eloquent
 */
class Domain extends Model
{
    use HasSlug;
    use SoftDeletes;


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('slug')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(DomainStats::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    protected $guarded = [];

    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }
}
