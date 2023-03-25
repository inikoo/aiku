<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:24:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Inventory\Stock;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Multitenancy\TenantCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\Tenant
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property bool $status
 * @property array $data
 * @property array $source
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $currency_id tenant accounting currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Central\TenantAccountingStats|null $accountingStats
 * @property-read \App\Models\Central\AdminUser|null $adminUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $agents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Central\CentralDomain> $centralDomains
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Central\CentralMedia> $centralMedia
 * @property-read \App\Models\Central\TenantFulfilmentStats|null $fulfilmentStats
 * @property-read \App\Models\Central\TenantInventoryStats|null $inventoryStats
 * @property-read \App\Models\Central\TenantMarketingStats|null $marketingStats
 * @property-read \App\Models\Central\TenantProcurementStats|null $procurementStats
 * @property-read \App\Models\Central\TenantProductionStats|null $productionStats
 * @property-read \App\Models\Central\TenantSalesStats|null $salesStats
 * @property-read \App\Models\Central\TenantStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supplier> $suppliers
 * @method static TenantCollection<int, static> all($columns = ['*'])
 * @method static TenantCollection<int, static> get($columns = ['*'])
 * @method static Builder|Tenant newModelQuery()
 * @method static Builder|Tenant newQuery()
 * @method static Builder|Tenant query()
 * @mixin \Eloquent
 */
class Tenant extends SpatieTenant
{
    use HasSlug;

    protected $casts = [
        'data'   => 'array',
        'source' => 'array',
    ];

    protected $attributes = [
        'data'   => '{}',
        'source' => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    protected $guarded = [];

    public function getDatabaseName(): string
    {
        return 'aiku_'.$this->slug;
    }

    public function stats(): HasOne
    {
        return $this->hasOne(TenantStats::class);
    }

    public function procurementStats(): HasOne
    {
        return $this->hasOne(TenantProcurementStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(TenantInventoryStats::class);
    }

    public function productionStats(): HasOne
    {
        return $this->hasOne(TenantProductionStats::class);
    }

    public function fulfilmentStats(): HasOne
    {
        return $this->hasOne(TenantFulfilmentStats::class);
    }

    public function marketingStats(): HasOne
    {
        return $this->hasOne(TenantMarketingStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(TenantSalesStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(TenantAccountingStats::class);
    }

    public function centralDomains(): HasMany
    {
        return $this->hasMany(CentralDomain::class);
    }

    public function suppliers(): MorphMany
    {
        return $this->morphMany(Supplier::class, 'owner');
    }

    public function agents(): MorphMany
    {
        return $this->morphMany(Agent::class, 'owner');
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner');
    }

    public function adminUser(): MorphOne
    {
        return $this->morphOne(AdminUser::class, 'userable');
    }

    public function accountsServiceProvider(): PaymentServiceProvider
    {
        return PaymentServiceProvider::where('data->service-code', 'accounts')->first();
    }

    public function centralMedia(): BelongsToMany
    {
        return $this->belongsToMany(CentralMedia::class)->withTimestamps();
    }
}
