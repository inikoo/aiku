<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:49:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Actions\Marketing\Shop\Hydrators\ShopHydrateDepartments;
use App\Enums\Marketing\ProductCategory\ProductCategoryStateEnum;
use App\Models\Sales\SalesStats;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\Department
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property int|null $image_id
 * @property int|null $shop_id
 * @property string $parent_type
 * @property int $parent_id
 * @property string $type
 * @property bool $is_family
 * @property ProductCategoryStateEnum|null $state
 * @property string|null $name
 * @property string|null $description
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_department_id
 * @property int|null $source_family_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCategory> $departments
 * @property-read Model|\Eloquent $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Product> $products
 * @property-read SalesStats|null $salesStats
 * @property-read SalesStats|null $salesTenantCurrencyStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \App\Models\Marketing\ProductCategoryStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Marketing\ProductCategoryFactory factory($count = null, $state = [])
 * @method static Builder|ProductCategory newModelQuery()
 * @method static Builder|ProductCategory newQuery()
 * @method static Builder|ProductCategory onlyTrashed()
 * @method static Builder|ProductCategory query()
 * @method static Builder|ProductCategory withTrashed()
 * @method static Builder|ProductCategory withoutTrashed()
 * @mixin \Eloquent
 */
class ProductCategory extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
        'state' => ProductCategoryStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::updated(function (ProductCategory $department) {
            if ($department->wasChanged('state')) {
                ShopHydrateDepartments::dispatch($department->shop);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(ProductCategoryStats::class);
    }

    public function salesStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope', 'sales');
    }

    public function salesTenantCurrencyStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope', 'sales-tenant-currency');
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function departments(): MorphMany
    {
        return $this->morphMany(ProductCategory::class, 'parent');
    }

    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'parent');
    }
}
