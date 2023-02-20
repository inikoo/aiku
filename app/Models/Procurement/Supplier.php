<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 09:03:12 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Actions\Central\Tenant\HydrateTenant;
use App\Models\Helpers\Address;
use App\Models\Traits\HasAddress;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * App\Models\Procurement\Supplier
 *
 * @property int $id
 * @property string $type
 * @property bool $status
 * @property string $slug
 * @property string $code
 * @property string $owner_type
 * @property int $owner_id
 * @property string $name
 * @property string|null $company_name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $address_id
 * @property array $location
 * @property int $currency_id
 * @property array $settings
 * @property array $shared_data
 * @property array $tenant_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $global_id
 * @property int|null $source_id
 * @property int|null $source_agent_id
 * @property-read Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @property-read string $formatted_address
 * @property-read \App\Models\Procurement\SupplierStats|null $stats
 * @method static Builder|Supplier newModelQuery()
 * @method static Builder|Supplier newQuery()
 * @method static \Illuminate\Database\Query\Builder|Supplier onlyTrashed()
 * @method static Builder|Supplier query()
 * @method static Builder|Supplier whereAddressId($value)
 * @method static Builder|Supplier whereCode($value)
 * @method static Builder|Supplier whereCompanyName($value)
 * @method static Builder|Supplier whereContactName($value)
 * @method static Builder|Supplier whereCreatedAt($value)
 * @method static Builder|Supplier whereCurrencyId($value)
 * @method static Builder|Supplier whereDeletedAt($value)
 * @method static Builder|Supplier whereEmail($value)
 * @method static Builder|Supplier whereGlobalId($value)
 * @method static Builder|Supplier whereId($value)
 * @method static Builder|Supplier whereLocation($value)
 * @method static Builder|Supplier whereName($value)
 * @method static Builder|Supplier whereOwnerId($value)
 * @method static Builder|Supplier whereOwnerType($value)
 * @method static Builder|Supplier wherePhone($value)
 * @method static Builder|Supplier whereSettings($value)
 * @method static Builder|Supplier whereSharedData($value)
 * @method static Builder|Supplier whereSlug($value)
 * @method static Builder|Supplier whereSourceAgentId($value)
 * @method static Builder|Supplier whereSourceId($value)
 * @method static Builder|Supplier whereStatus($value)
 * @method static Builder|Supplier whereTenantData($value)
 * @method static Builder|Supplier whereType($value)
 * @method static Builder|Supplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Supplier withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Supplier withoutTrashed()
 * @mixin Eloquent
 */
class Supplier extends Model
{
    use SoftDeletes;
    use HasAddress;
    use HasSlug;
    use TenantConnection;

    protected $casts = [
        'shared_data' => 'array',
        'tenant_data' => 'array',
        'settings'    => 'array',
        'location'    => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'shared_data' => '{}',
        'tenant_data' => '{}',
        'settings'    => '{}',
        'location'    => '{}',

    ];

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function () {
                HydrateTenant::make()->procurementStats();
            }
        );
        static::deleted(
            function () {
                HydrateTenant::make()->procurementStats();
            }
        );

        static::updated(function (Supplier $supplier) {
            if (!$supplier->wasRecentlyCreated) {
                if ($supplier->wasChanged('status')) {
                    HydrateTenant::make()->procurementStats();
                }
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(SupplierStats::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }


}
