<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:29:00 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $image_id
 * @property int|null $shop_id
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\Collection> $collections
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \App\Models\Catalogue\CollectionCategorySalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\CollectionCategoryStats|null $stats
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategory withoutTrashed()
 * @mixin \Eloquent
 */
class CollectionCategory extends Model implements Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasHistory;
    use InShop;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(128);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(CollectionCategoryStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(CollectionCategorySalesIntervals::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

}
