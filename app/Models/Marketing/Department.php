<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:49:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Actions\Marketing\Shop\HydrateShop;
use App\Enums\Marketing\Department\DepartmentStateEnum;
use App\Models\Sales\SalesStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Family> $families
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Product> $products
 * @property-read SalesStats|null $salesStats
 * @property-read SalesStats|null $salesTenantCurrencyStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \App\Models\Marketing\DepartmentStats|null $stats
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department onlyTrashed()
 * @method static Builder|Department query()
 * @method static Builder|Department withTrashed()
 * @method static Builder|Department withoutTrashed()
 * @mixin \Eloquent
 */
class Department extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;

    protected $guarded = [];

    protected $casts = [
        'data'  => 'array',
        'state' => DepartmentStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::created(
            function (Department $department) {
                HydrateShop::make()->departmentsStats($department->shop);
            }
        );
        static::deleted(
            function (Department $department) {
                HydrateShop::make()->departmentsStats($department->shop);
            }
        );
        static::updated(function (Department $department) {
            if ($department->wasChanged('state')) {
                HydrateShop::make()->departmentsStats($department->shop);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(DepartmentStats::class);
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
