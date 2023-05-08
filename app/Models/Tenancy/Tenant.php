<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Accounting\PaymentServiceProvider;
use App\Models\AgentTenant;
use App\Models\Assets\Currency;
use App\Models\Central\CentralDomain;
use App\Models\Central\CentralMedia;
use App\Models\Inventory\Stock;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\SupplierProductTenant;
use App\Models\SupplierTenant;
use App\Models\SysAdmin\SysUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * App\Models\Tenancy\Tenant
 *
 * @property int $id
 * @property int $group_id
 * @property string $ulid
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string|null $email
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
 * @property-read \App\Models\Tenancy\TenantAccountingStats|null $accountingStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $agents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CentralDomain> $centralDomains
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, CentralMedia> $centralMedia
 * @property-read Currency $currency
 * @property-read \App\Models\Tenancy\TenantFulfilmentStats|null $fulfilmentStats
 * @property-read \App\Models\Tenancy\Group $group
 * @property-read \App\Models\Tenancy\TenantInventoryStats|null $inventoryStats
 * @property-read \App\Models\Tenancy\TenantMailStats|null $mailStats
 * @property-read \App\Models\Tenancy\TenantMarketingStats|null $marketingStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $myAgents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supplier> $mySuppliers
 * @property-read \App\Models\Tenancy\TenantProcurementStats|null $procurementStats
 * @property-read \App\Models\Tenancy\TenantProductionStats|null $productionStats
 * @property-read \App\Models\Tenancy\TenantSalesStats|null $salesStats
 * @property-read \App\Models\Tenancy\TenantStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierProduct> $supplierProducts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supplier> $suppliers
 * @property-read SysUser|null $sysUser
 * @method static TenantCollection<int, static> all($columns = ['*'])
 * @method static \Database\Factories\Tenancy\TenantFactory factory($count = null, $state = [])
 * @method static TenantCollection<int, static> get($columns = ['*'])
 * @method static Builder|Tenant newModelQuery()
 * @method static Builder|Tenant newQuery()
 * @method static Builder|Tenant query()
 * @mixin \Eloquent
 */
class Tenant extends SpatieTenant
{
    use HasFactory;
    use HasSlug;

    protected $casts = [
        'data'   => 'array',
        'source' => 'array',
    ];

    protected $attributes = [
        'data'   => '{}',
        'source' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }



    public function schema(): string
    {
        return 'tenant_'.preg_replace('/-/', '_', $this->slug);
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

    public function mailStats(): HasOne
    {
        return $this->hasOne(TenantMailStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(TenantSalesStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(TenantAccountingStats::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function centralDomains(): HasMany
    {
        return $this->hasMany(CentralDomain::class);
    }

    public function mySuppliers(): MorphMany
    {
        return $this->morphMany(Supplier::class, 'owner');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)
            ->using(SupplierTenant::class)
            ->withPivot(['source_id'])
            ->withTimestamps();
    }

    public function myAgents(): HasMany
    {
        return $this->hasMany(Agent::class, 'owner_id');
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class)
            ->using(AgentTenant::class)
            ->withPivot(['source_id'])
            ->withTimestamps();
    }

    public function supplierProducts(): BelongsToMany
    {
        return $this->belongsToMany(SupplierProduct::class)
            ->using(SupplierProductTenant::class)
            ->withPivot(['source_id'])
            ->withTimestamps();
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner');
    }

    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }

    public function accountsServiceProvider(): PaymentServiceProvider
    {
        return PaymentServiceProvider::where('data->service-code', 'accounts')->first();
    }

    public function centralMedia(): BelongsToMany
    {
        return $this->belongsToMany(CentralMedia::class)->withTimestamps();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
