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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\TenantCollection;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory;

    use HasDatabase, HasDomains;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'numeric_id',
            'code',
            'type',
            'name',
            'country_id',
            'language_id',
            'timezone_id',
            'currency_id',
        ];
    }

    /*
    public function tenantUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            CentralUser::class,
            'tenant_users',

            'tenant_id',
            'global_user_id',

            'id',
            'global_id'
        )
            ->using(TenantUser::class);
    }
    */

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
        return $this->morphMany(Supplier::class, 'owner', 'owner_type', 'owner_id', 'numeric_id');
    }

    public function agents(): MorphMany
    {
        return $this->morphMany(Agent::class, 'owner', 'owner_type', 'owner_id', 'numeric_id');
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner', 'owner_type', 'owner_id', 'numeric_id');
    }

    public function adminUser(): MorphOne
    {
        return $this->morphOne(AdminUser::class, 'userable', null, null, 'numeric_id');
    }

    public function accountsServiceProvider(): PaymentServiceProvider{
        return  PaymentServiceProvider::where('data->service-code', 'accounts')->first();
    }

}
