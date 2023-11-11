<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:53:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Actions\Tenancy\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Assets\Currency;
use App\Models\Helpers\GroupAddress;
use App\Models\Media\GroupMedia;
use App\Models\Search\UniversalSearch;
use App\Models\Tenancy\Tenant;
use App\Models\Traits\HasGroupAddress;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasPhoto;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\Agent
 *
 * @property int $id
 * @property bool $status
 * @property bool $is_private
 * @property string $slug
 * @property string $code
 * @property string $owner_type Who can edit this model Tenant|Agent|Supplier
 * @property int $owner_id
 * @property string|null $name
 * @property int|null $image_id
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property string|null $contact_website
 * @property int|null $address_id
 * @property array $location
 * @property int $currency_id
 * @property array $settings
 * @property array $shared_data
 * @property array $tenant_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_type
 * @property int|null $source_id
 * @property-read Collection<int, GroupAddress> $addresses
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read Currency $currency
 * @property-read array $es_audits
 * @property-read MediaCollection<int, GroupMedia> $media
 * @property-read Tenant|null $owner
 * @property-read Collection<int, \App\Models\Procurement\SupplierProduct> $products
 * @property-read Collection<int, \App\Models\Procurement\PurchaseOrder> $purchaseOrders
 * @property-read \App\Models\Procurement\AgentStats|null $stats
 * @property-read Collection<int, \App\Models\Procurement\SupplierDelivery> $supplierDeliveries
 * @property-read Collection<int, \App\Models\Procurement\Supplier> $suppliers
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Procurement\AgentFactory factory($count = null, $state = [])
 * @method static Builder|Agent newModelQuery()
 * @method static Builder|Agent newQuery()
 * @method static Builder|Agent onlyTrashed()
 * @method static Builder|Agent query()
 * @method static Builder|Agent withTrashed()
 * @method static Builder|Agent withoutTrashed()
 * @mixin Eloquent
 */
class Agent extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasGroupAddress;
    use HasSlug;

    use HasUniversalSearch;
    use HasPhoto;
    use HasFactory;
    use HasHistory;

    protected $casts = [
        'shared_data' => 'array',
        'tenant_data' => 'array',
        'settings'    => 'array',
        'location'    => 'array',
        'status'      => 'boolean',
        'is_private'  => 'boolean'
    ];

    protected $attributes = [
        'shared_data' => '{}',
        'tenant_data' => '{}',
        'settings'    => '{}',
        'location'    => '{}',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(
            function (Agent $agent) {
                $agent->name = $agent->company_name == '' ? $agent->contact_name : $agent->company_name;
            }
        );

        static::updated(function (Agent $agent) {
            if (!$agent->wasRecentlyCreated) {
                if ($agent->wasChanged(['contact_name', 'company_name'])) {
                    $agent->name = $agent->company_name == '' ? $agent->contact_name : $agent->company_name;
                }

                if ($agent->wasChanged('status')) {
                    TenantHydrateProcurement::dispatch(app('currentTenant'));
                    GroupHydrateProcurement::run(app('currentTenant')->group);
                }
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(AgentStats::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'owner_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function purchaseOrders(): MorphMany
    {
        return $this->morphMany(PurchaseOrder::class, 'provider');
    }

    public function supplierDeliveries(): MorphMany
    {
        return $this->morphMany(SupplierDelivery::class, 'provider');
    }

    public function tenantIds(): array
    {
        return AgentTenant::where('agent_id', $this->id)->get()->pluck('tenant_id')->all();
    }

}
