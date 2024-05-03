<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 19:32:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Models\Assets\Currency;
use App\Models\Helpers\Issue;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierDelivery;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasAddresses;
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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SupplyChain\Supplier
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $agent_id
 * @property bool $status
 * @property string $slug
 * @property string $code
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
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_slug
 * @property string|null $source_id
 * @property-read Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read \App\Models\SupplyChain\Agent|null $agent
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Currency $currency
 * @property-read Group $group
 * @property-read Collection<int, Issue> $issues
 * @property-read MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read Collection<int, OrgSupplier> $orgSuppliers
 * @property-read Collection<int, \App\Models\SupplyChain\SupplierProduct> $products
 * @property-read Collection<int, PurchaseOrder> $purchaseOrders
 * @property-read \App\Models\SupplyChain\SupplierStats|null $stats
 * @property-read Collection<int, SupplierDelivery> $supplierDeliveries
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\SupplyChain\SupplierFactory factory($count = null, $state = [])
 * @method static Builder|Supplier newModelQuery()
 * @method static Builder|Supplier newQuery()
 * @method static Builder|Supplier onlyTrashed()
 * @method static Builder|Supplier query()
 * @method static Builder|Supplier withTrashed()
 * @method static Builder|Supplier withoutTrashed()
 * @mixin Eloquent
 */
class Supplier extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasAddresses;
    use HasSlug;
    use HasUniversalSearch;
    use HasPhoto;
    use HasFactory;
    use HasHistory;

    protected $casts = [
        'data'        => 'array',
        'settings'    => 'array',
        'location'    => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'data'        => '{}',
        'settings'    => '{}',
        'location'    => '{}',

    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(
            function (Supplier $supplier) {
                $supplier->name = $supplier->company_name == '' ? $supplier->contact_name : $supplier->company_name;
            }
        );

        static::updated(function (Supplier $supplier) {
            if (!$supplier->wasRecentlyCreated) {
                if ($supplier->wasChanged(['contact_name', 'company_name'])) {
                    $supplier->name = $supplier->company_name == '' ? $supplier->contact_name : $supplier->company_name;
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(SupplierStats::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function purchaseOrders(): MorphMany
    {
        return $this->morphMany(PurchaseOrder::class, 'parent');
    }

    public function supplierDeliveries(): MorphMany
    {
        return $this->morphMany(SupplierDelivery::class, 'parent');
    }

    public function orgSuppliers(): HasMany
    {
        return $this->hasMany(OrgSupplier::class);
    }


}
