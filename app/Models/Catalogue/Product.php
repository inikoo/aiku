<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Models\Goods\TradeUnit;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $asset_id
 * @property int|null $family_id
 * @property int|null $department_id
 * @property bool $status
 * @property ProductStateEnum $state
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $price
 * @property string $units
 * @property string $unit
 * @property array $data
 * @property array $settings
 * @property int $currency_id
 * @property int|null $current_historic_asset_id
 * @property int|null $product_variant_id
 * @property string|null $barcode mirror from trade_unit
 * @property string|null $rrp RRP per outer
 * @property int|null $image_id
 * @property ProductUnitRelationshipType|null $unit_relationship_type
 * @property int|null $available_quantity outer available quantity for sale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read \App\Models\Catalogue\Asset|null $asset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\Helpers\Currency $currency
 * @property-read \App\Models\Catalogue\ProductCategory|null $department
 * @property-read \App\Models\Catalogue\ProductCategory|null $family
 * @property-read Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricAsset> $historicAssets
 * @property-read \App\Models\Studio\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $images
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $media
 * @property-read Organisation $organisation
 * @property-read \App\Models\Catalogue\ProductVariant|null $productVariant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\ProductVariant> $productVariants
 * @property-read \App\Models\Catalogue\ProductSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\ProductStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TradeUnit> $tradeUnits
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Catalogue\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model implements Auditable, HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use InAssetModel;
    use HasHistory;
    use HasFactory;
    use HasImage;

    protected $guarded = [];

    protected $casts = [
        'data'                   => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
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
                return $this->code.'-'.$this->shop->cpde;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProductStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ProductSalesIntervals::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'product_trade_unit',
        )->withPivot(['units', 'notes'])->withTimestamps();
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'department_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'family_id');
    }


}
