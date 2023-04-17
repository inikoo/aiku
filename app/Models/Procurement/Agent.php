<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:53:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Helpers\Address;
use App\Models\PurchaseOrder;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasPhoto;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\Agent
 *
 * @property int $id
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
 * @property int|null $image_id
 * @property int $currency_id
 * @property array $settings
 * @property array $shared_data
 * @property array $tenant_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $central_agent_id
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read Model|\Eloquent $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\SupplierProduct> $products
 * @property-read \App\Models\Procurement\AgentStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\Supplier> $suppliers
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|Agent newModelQuery()
 * @method static Builder|Agent newQuery()
 * @method static Builder|Agent onlyTrashed()
 * @method static Builder|Agent query()
 * @method static Builder|Agent withTrashed()
 * @method static Builder|Agent withoutTrashed()
 * @mixin \Eloquent
 */
class Agent extends Model implements HasMedia
{
    use SoftDeletes;
    use HasAddress;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasPhoto;

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
        static::updated(function (Agent $agent) {
            if (!$agent->wasRecentlyCreated) {
                if ($agent->wasChanged('status')) {
                    TenantHydrateProcurement::dispatch(app('currentTenant'));
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

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function purchaseOrder(): MorphMany
    {
        return $this->morphMany(PurchaseOrder::class, 'provider');
    }

}
