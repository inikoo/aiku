<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Grouping;

use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Currency;
use App\Models\Central\Domain;
use App\Models\Dispatch\Shipper;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\Media\Media;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierDelivery;
use App\Models\SysAdmin\SysUser;
use App\Models\Traits\HasLogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Grouping\Organisation
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
 * @property array $settings
 * @property array $source
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $currency_id customer accounting currency
 * @property int|null $logo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Grouping\OrganisationAccountingStats|null $accountingStats
 * @property-read \App\Models\Grouping\OrganisationCRMStats|null $crmStats
 * @property-read Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Domain> $domains
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 * @property-read \App\Models\Grouping\OrganisationFulfilmentStats|null $fulfilmentStats
 * @property-read \App\Models\Grouping\Group $group
 * @property-read \App\Models\Grouping\OrganisationHumanResourcesStats|null $humanResourcesStats
 * @property-read \App\Models\Grouping\OrganisationInventoryStats|null $inventoryStats
 * @property-read Media|null $logo
 * @property-read \App\Models\Grouping\OrganisationMailStats|null $mailStats
 * @property-read \App\Models\Grouping\OrganisationMarketStats|null $marketStats
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read \App\Models\Grouping\OrganisationProcurementStats|null $procurementStats
 * @property-read \App\Models\Grouping\OrganisationProductionStats|null $productionStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseOrder> $purchaseOrders
 * @property-read \App\Models\Grouping\OrganisationSalesStats|null $salesStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipper> $shippers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shop> $shops
 * @property-read \App\Models\Grouping\OrganisationStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierDelivery> $supplierDeliveries
 * @property-read SysUser|null $sysUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Warehouse> $warehouses
 * @property-read \App\Models\Grouping\OrganisationWebStats|null $webStats
 * @method static \Database\Factories\Grouping\OrganisationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Organisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Organisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Organisation query()
 * @mixin \Eloquent
 */
class Organisation extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use HasLogo;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'source'   => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
        'source'   => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }



    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrganisationStats::class);
    }

    public function humanResourcesStats(): HasOne
    {
        return $this->hasOne(OrganisationHumanResourcesStats::class);
    }

    public function procurementStats(): HasOne
    {
        return $this->hasOne(OrganisationProcurementStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(OrganisationInventoryStats::class);
    }

    public function productionStats(): HasOne
    {
        return $this->hasOne(OrganisationProductionStats::class);
    }

    public function fulfilmentStats(): HasOne
    {
        return $this->hasOne(OrganisationFulfilmentStats::class);
    }

    public function marketStats(): HasOne
    {
        return $this->hasOne(OrganisationMarketStats::class);
    }

    public function mailStats(): HasOne
    {
        return $this->hasOne(OrganisationMailStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(OrganisationSalesStats::class);
    }

    public function crmStats(): HasOne
    {
        return $this->hasOne(OrganisationCRMStats::class);
    }

    public function webStats(): HasOne
    {
        return $this->hasOne(OrganisationWebStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(OrganisationAccountingStats::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }

    public function accountsServiceProvider(): PaymentServiceProvider
    {
        return PaymentServiceProvider::where('data->service-code', 'accounts')->first();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function logo(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'logo_id');
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierDeliveries(): HasMany
    {
        return $this->hasMany(SupplierDelivery::class);
    }

    public function shippers(): HasMany
    {
        return $this->hasMany(Shipper::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

}
