<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Media\Media;
use App\Models\ProductSalesStats;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasImages;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property ProductTypeEnum $type
 * @property string $owner_type
 * @property int $owner_id
 * @property string $parent_type
 * @property int $parent_id
 * @property int|null $current_historic_outer_id
 * @property ProductStateEnum $state
 * @property bool $status
 * @property ProductUnitRelationshipType|null $unit_relationship_type
 * @property int|null $main_outer_id
 * @property int|null $available_outers (main outer in physical goods)
 * @property string|null $price unit price (main outer in physical goods)
 * @property int|null $image_id
 * @property string|null $rrp RRP per outer
 * @property array $settings
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Barcode> $barcode
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Market\HistoricOuter> $historicOuters
 * @property-read MediaCollection<int, Media> $images
 * @property-read MediaCollection<int, Media> $media
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Market\Outer> $outers
 * @property-read ProductSalesStats|null $salesStats
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

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'product_trade_unit',
        )->withPivot(['units_per_main_outer','notes'])->withTimestamps();
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(ProductSalesStats::class);
    }

    public function historicOuters(): HasMany
    {
        return $this->hasMany(HistoricOuter::class);
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

}
