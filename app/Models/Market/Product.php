<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Models\Fulfilment\Rental;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Media\Media;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasImages;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Market\Product
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $slug
 * @property ProductTypeEnum $type
 * @property string $owner_type
 * @property int $owner_id
 * @property string $parent_type
 * @property int $parent_id
 * @property string $outerable_type
 * @property int|null $current_historic_outerable_id
 * @property ProductStateEnum $state
 * @property bool $status
 * @property ProductUnitRelationshipType|null $unit_relationship_type
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $main_outerable_id
 * @property string|null $main_outerable_price main outer price
 * @property int $currency_id
 * @property string|null $main_outerable_unit
 * @property int|null $main_outerable_available_quantity
 * @property int|null $image_id
 * @property string|null $rrp RRP per outer
 * @property array $settings
 * @property array $data
 * @property int|null $family_id
 * @property int|null $department_id
 * @property bool $is_legacy
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read Collection<int, Barcode> $barcode
 * @property-read \App\Models\Market\HistoricOuterable|null $currentHistoricOuterable
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Market\HistoricOuterable> $historicOuters
 * @property-read MediaCollection<int, Media> $images
 * @property-read Model|\Eloquent $mainOuterable
 * @property-read MediaCollection<int, Media> $media
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Market\Outer> $outers
 * @property-read Rental|null $rental
 * @property-read \App\Models\Market\ProductSalesIntervals|null $salesStats
 * @property-read \App\Models\Market\Service|null $service
 * @property-read \App\Models\Market\Shop|null $shop
 * @property-read \App\Models\Market\ProductStats|null $stats
 * @property-read Collection<int, TradeUnit> $tradeUnits
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Market\ProductFactory factory($count = null, $state = [])
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product query()
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @mixin Eloquent
 */
class Product extends Model implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasImages;
    use HasFactory;
    use InShop;

    protected $casts = [
        'data'                   => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
        'type'                   => ProductTypeEnum::class,
        'state'                  => ProductStateEnum::class,
        'unit_relationship_type' => ProductUnitRelationshipType::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->shop->slug.'-'.$this->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    protected $guarded = [];


    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'product_trade_unit',
        )->withPivot(['units_per_main_outer', 'notes'])->withTimestamps();
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ProductSalesIntervals::class);
    }

    public function historicOuters(): HasMany
    {
        return $this->hasMany(HistoricOuterable::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProductStats::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_product')->withTimestamps()
            ->withPivot(['public', 'owner_type', 'owner_id'])
            ->wherePivot('type', 'image');
    }

    public function barcode(): MorphToMany
    {
        return $this->morphToMany(Barcode::class, 'barcodeable');
    }

    public function outers(): HasMany
    {
        return $this->hasMany(Outer::class);
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class, 'id', 'main_outerable_id');
    }

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class, 'id', 'main_outerable_id');
    }

    public function mainOuterable(): MorphTo
    {
        return $this->morphTo(type: 'outerable_type', id: 'main_outerable_id');
    }

    public function currentHistoricOuterable(): BelongsTo
    {
        return $this->belongsTo(HistoricOuterable::class);
    }

}
