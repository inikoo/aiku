<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:24:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\TenantCollection;

/**
 * App\Models\Central\Tenant
 *
 * @property int $id
 * @property string $code
 * @property string $uuid
 * @property string $name
 * @property array $data
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $currency_id tenant accounting currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Central\CentralDomain[] $centralDomains
 * @property-read int|null $central_domains_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Stancl\Tenancy\Database\Models\Domain[] $domains
 * @property-read int|null $domains_count
 * @property-read \App\Models\Central\TenantInventoryStats|null $inventoryStats
 * @property-read \App\Models\Central\TenantMarketingStats|null $marketingStats
 * @property-read \App\Models\Central\TenantSalesStats|null $salesStats
 * @property-read \App\Models\Central\TenantStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Central\CentralUser[] $users
 * @property-read int|null $users_count
 * @method static TenantCollection|static[] all($columns = ['*'])
 * @method static TenantCollection|static[] get($columns = ['*'])
 * @method static Builder|Tenant newModelQuery()
 * @method static Builder|Tenant newQuery()
 * @method static Builder|Tenant query()
 * @method static Builder|Tenant whereCode($value)
 * @method static Builder|Tenant whereCountryId($value)
 * @method static Builder|Tenant whereCreatedAt($value)
 * @method static Builder|Tenant whereCurrencyId($value)
 * @method static Builder|Tenant whereData($value)
 * @method static Builder|Tenant whereDeletedAt($value)
 * @method static Builder|Tenant whereId($value)
 * @method static Builder|Tenant whereLanguageId($value)
 * @method static Builder|Tenant whereName($value)
 * @method static Builder|Tenant whereTimezoneId($value)
 * @method static Builder|Tenant whereUpdatedAt($value)
 * @method static Builder|Tenant whereUuid($value)
 * @mixin \Eloquent
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory;
    use HasApiTokens;

    use HasDatabase, HasDomains;

    protected $casts = [
        'data'     => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

    public static function getCustomColumns(): array
    {
        return [
            'code',
            'uuid',
            'type',
            'name',
            'country_id',
            'language_id',
            'timezone_id',
            'currency_id',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            CentralUser::class,
            'tenant_users',
            'tenant_id', 'global_user_id',
            'id', 'global_id')
            ->using(TenantUser::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(TenantStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(TenantInventoryStats::class);
    }

    public function marketingStats(): HasOne
    {
        return $this->hasOne(TenantMarketingStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(TenantSalesStats::class);
    }


    public function centralDomains(): HasMany
    {
        return $this->hasMany(CentralDomain::class);
    }

}
