<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Outer\OuterStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\Outer
 *
 * @property int $id
 * @property bool $is_main
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $product_id
 * @property int|null $current_historic_outerable_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property OuterStateEnum $state
 * @property string|null $main_outer_ratio number of outers in relation to main outer
 * @property string $price outer price
 * @property string|null $unit
 * @property int|null $available_quantity outer available quantity for sale
 * @property int $number_historic_outerables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read \App\Models\Catalogue\HistoricOuterable|null $currentHistoricOuterable
 * @property-read Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricOuterable> $historicRecords
 * @property-read Organisation $organisation
 * @property-read \App\Models\Catalogue\Product|null $product
 * @property-read \App\Models\Catalogue\OuterSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
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
            ->generateSlugsFrom(function () {
                return $this->shop->slug . '-' . $this->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }


    public function salesIntervals(): HasOne
    {
        return $this->hasOne(OuterSalesIntervals::class);
    }

    public function historicRecords(): MorphMany
    {
        return $this->morphMany(HistoricOuterable::class, 'outerable');
    }

    public function currentHistoricOuterable(): BelongsTo
    {
        return $this->belongsTo(HistoricOuterable::class);
    }


}
