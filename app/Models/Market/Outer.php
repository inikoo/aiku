<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Outer\OuterStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Market\Outer
 *
 * @property int $id
 * @property bool $is_main
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $product_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property OuterStateEnum $state
 * @property string|null $main_outer_ratio number of outers in relation to main outer
 * @property string $price outer price
 * @property int|null $available
 * @property int $number_historic_outerables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Market\HistoricOuterable> $historicRecords
 * @property-read Organisation $organisation
 * @property-read \App\Models\Market\Product|null $product
 * @property-read \App\Models\Market\OuterSalesStats|null $salesStats
 * @property-read \App\Models\Market\Shop|null $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Outer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Outer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Outer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Outer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Outer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Outer withoutTrashed()
 * @mixin \Eloquent
 */
class Outer extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use IsOuterable;

    protected $guarded = [];

    protected $casts = [
        'state'       => OuterStateEnum::class

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
            ->slugsShouldBeNoLongerThan(64);
    }


    public function salesStats(): HasOne
    {
        return $this->hasOne(OuterSalesStats::class);
    }

    public function historicRecords(): HasMany
    {
        return $this->hasMany(HistoricOuterable::class);
    }


}
