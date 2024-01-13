<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 17:25:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Dispatch\Shipper;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\Media\Media;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Traits\HasLogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\Organisation
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
 * @property-read \App\Models\SysAdmin\OrganisationAccountingStats|null $accountingStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SysAdmin\OrganisationAuthorisedModels> $authorisedModels
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ClockingMachine> $clockingMachines
 * @property-read Country $country
 * @property-read \App\Models\SysAdmin\OrganisationCRMStats|null $crmStats
 * @property-read Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Customer> $customers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 * @property-read \App\Models\SysAdmin\OrganisationFulfilmentStats|null $fulfilmentStats
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\OrganisationHumanResourcesStats|null $humanResourcesStats
 * @property-read \App\Models\SysAdmin\OrganisationInventoryStats|null $inventoryStats
 * @property-read Language $language
 * @property-read Media|null $logo
 * @property-read \App\Models\SysAdmin\OrganisationMailStats|null $mailStats
 * @property-read \App\Models\SysAdmin\OrganisationMarketStats|null $marketStats
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentServiceProvider> $paymentServiceProviders
 * @property-read \App\Models\SysAdmin\OrganisationProcurementStats|null $procurementStats
 * @property-read \App\Models\SysAdmin\OrganisationProductionStats|null $productionStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Prospect> $prospects
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseOrder> $purchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SysAdmin\Role> $roles
 * @property-read \App\Models\SysAdmin\OrganisationSalesStats|null $salesStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipper> $shippers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shop> $shops
 * @property-read \App\Models\SysAdmin\OrganisationStats|null $stats
 * @property-read \App\Models\SysAdmin\SysUser|null $sysUser
 * @property-read Timezone $timezone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Warehouse> $warehouses
 * @property-read \App\Models\SysAdmin\OrganisationWebStats|null $webStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Workplace> $workplaces
 * @method static \Database\Factories\SysAdmin\OrganisationFactory factory($count = null, $state = [])
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

    public function workplaces(): HasMany
    {
        return $this->hasMany(Workplace::class);
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function timezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }

    public function accountsServiceProvider(): PaymentServiceProvider
    {
        return PaymentServiceProvider::where('type', PaymentServiceProviderTypeEnum::ACCOUNT)->first();
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

    public function shippers(): HasMany
    {
        return $this->hasMany(Shipper::class);
    }

    public function clockingMachines(): HasMany
    {
        return $this->hasMany(ClockingMachine::class);
    }

    public function paymentServiceProviders(): HasMany
    {
        return $this->hasMany(PaymentServiceProvider::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'scope');
    }

    public function authorisedModels(): HasMany
    {
        return $this->hasMany(OrganisationAuthorisedModels::class, 'org_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }


}
