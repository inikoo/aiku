<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:01:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;


use App\Actions\Marketing\Department\HydrateDepartment;
use App\Actions\Marketing\Shop\HydrateShop;
use App\Models\Sales\SalesStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\Family
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property int|null $shop_id
 * @property int|null $department_id
 * @property string|null $state
 * @property string|null $name
 * @property string|null $description
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Marketing\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Product> $products
 * @property-read int|null $products_count
 * @property-read SalesStats|null $salesStats
 * @property-read SalesStats|null $salesTenantCurrencyStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \App\Models\Marketing\FamilyStats|null $stats
 * @method static Builder|Family newModelQuery()
 * @method static Builder|Family newQuery()
 * @method static Builder|Family onlyTrashed()
 * @method static Builder|Family query()
 * @method static Builder|Family whereCode($value)
 * @method static Builder|Family whereCreatedAt($value)
 * @method static Builder|Family whereData($value)
 * @method static Builder|Family whereDeletedAt($value)
 * @method static Builder|Family whereDepartmentId($value)
 * @method static Builder|Family whereDescription($value)
 * @method static Builder|Family whereId($value)
 * @method static Builder|Family whereName($value)
 * @method static Builder|Family whereShopId($value)
 * @method static Builder|Family whereSlug($value)
 * @method static Builder|Family whereSourceId($value)
 * @method static Builder|Family whereState($value)
 * @method static Builder|Family whereUpdatedAt($value)
 * @method static Builder|Family withTrashed()
 * @method static Builder|Family withoutTrashed()
 * @mixin \Eloquent
 */
class Family extends Model
{
    use HasSlug;
    use SoftDeletes;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function (Family $family) {
                if ($family->department_id) {
                    HydrateDepartment::make()->familiesStats($family->department);
                }
                HydrateShop::make()->familiesStats($family->shop);
            }
        );
        static::deleted(
            function (Family $family) {
                if ($family->department_id) {
                    HydrateDepartment::make()->familiesStats($family->department);
                }
                HydrateShop::make()->familiesStats($family->shop);
            }
        );
        static::updated(function (Family $family) {
            if ($family->wasChanged('state')) {
                if ($family->department_id) {
                    HydrateDepartment::make()->familiesStats($family->department);
                }
                HydrateShop::make()->familiesStats($family->shop);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(FamilyStats::class);
    }

    public function salesStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope', 'sales');
    }

    public function salesTenantCurrencyStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope', 'sales-tenant-currency');
    }


}
