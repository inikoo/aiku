<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 12:31:32 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\ProductVariant\ProductVariantStateEnum;
use App\Enums\Catalogue\ProductVariant\ProductVariantUnitRelationshipType;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Currency;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property int|null $shop_id
 * @property int|null $asset_id
 * @property int|null $family_id
 * @property int|null $department_id
 * @property bool $is_main
 * @property string $ratio
 * @property int|null $product_id
 * @property bool $status
 * @property ProductVariantStateEnum $state
 * @property ProductVariantUnitRelationshipType|null $unit_relationship_type
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $price
 * @property string $unit
 * @property int $units
 * @property int $currency_id
 * @property int|null $current_historic_asset_id
 * @property int|null $current_historic_product_variant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Currency $currency
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricProductVariant> $historicProductVariants
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Product|null $product
 * @property-read \App\Models\Catalogue\ProductVariantSalesInterval|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\ProductVariantStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TradeUnit> $tradeUnits
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant withoutTrashed()
 * @mixin \Eloquent
 */
class ProductVariant extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasHistory;
    use InShop;

    protected $guarded = [];

    protected $casts = [
        'data'                   => 'array',
        'status'                 => 'boolean',
        'state'                  => ProductVariantStateEnum::class,
        'unit_relationship_type' => ProductVariantUnitRelationshipType::class
    ];

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

    public function stats(): HasOne
    {
        return $this->hasOne(ProductVariantStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ProductVariantSalesInterval::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'product_variant_trade_unit',
        )->withPivot(['units', 'notes'])->withTimestamps();
    }

    public function historicProductVariants(): HasMany
    {
        return $this->hasMany(HistoricProductVariant::class);
    }

}
