<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Actions\Marketing\Family\Hydrators\FamilyHydrateProducts;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateProducts;
use App\Enums\Marketing\Product\ProductStateEnum;
use App\Models\Sales\SalesStats;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\Product
 *
 * @property int $id
 * @property string|null $slug
 * @property string $owner_type
 * @property int $owner_id
 * @property int|null $current_historic_product_id
 * @property int|null $shop_id
 * @property int|null $family_id
 * @property string|null $state
 * @property bool|null $status
 * @property string $composition
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $units units per outer
 * @property string $price unit price
 * @property string|null $rrp RRP per outer
 * @property int|null $available
 * @property int|null $image_id
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Marketing\Family|null $family
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\HistoricProduct> $historicRecords
 * @property-read SalesStats|null $salesStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \App\Models\Marketing\ProductStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\TradeUnit> $tradeUnits
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product query()
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;


    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'status'   => 'boolean',
        'state'    => ProductStateEnum::class
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
            ->slugsShouldBeNoLongerThan(64);
    }

    protected static function booted()
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

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(TradeUnit::class)->withPivot('quantity')->withTimestamps();
    }

    public function salesStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope', 'sales');
    }

    public function historicRecords(): HasMany
    {
        return $this->hasMany(HistoricProduct::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProductStats::class);
    }
}
