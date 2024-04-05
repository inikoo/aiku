<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTradeUnitCompositionEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Media\Media;
use App\Models\ProductSalesStats;
use App\Models\Search\UniversalSearch;
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
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property ProductTypeEnum $type
 * @property int $owner_id
 * @property string $owner_type
 * @property int $parent_id
 * @property string $parent_type
 * @property int|null $current_historic_product_id
 * @property int|null $shop_id
 * @property ProductStateEnum|null $state
 * @property bool|null $status
 * @property ProductTradeUnitCompositionEnum $trade_unit_composition
 * @property string|null $units units per outer
 * @property string $price unit price
 * @property string|null $rrp RRP per outer
 * @property int|null $available
 * @property int|null $image_id
 * @property array $settings
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Barcode> $barcode
 * @property-read Collection<int, \App\Models\Market\HistoricProduct> $historicRecords
 * @property-read MediaCollection<int, Media> $images
 * @property-read MediaCollection<int, Media> $media
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
        'trade_unit_composition' => ProductTradeUnitCompositionEnum::class
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
    /*
        protected static function booted(): void
        {
            static::updated(function (Product $product) {
                if ($product->wasChanged('state')) {

                    if ($product->family_id) {
                        FamilyHydrateProducts::dispatch($product->family);
                     }
                    ShopHydrateProducts::dispatch($product->shop);
                }
            });
        }
    */

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'product_trade_unit',
        )->withPivot(['quantity','notes'])->withTimestamps();
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(ProductSalesStats::class);
    }

    public function historicRecords(): HasMany
    {
        return $this->hasMany(HistoricProduct::class);
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

}
