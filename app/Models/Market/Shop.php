<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:29:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Market;

use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Accounting\PaymentServiceProviderShop;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Timezone;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\SysAdmin\Organisation;
use App\Models\Helpers\Address;
use App\Models\Helpers\Issue;
use App\Models\Helpers\SerialReference;
use App\Models\Helpers\TaxNumber;
use App\Models\Mail\Outbox;
use App\Models\Marketing\OfferCampaign;
use App\Models\OMS\Order;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\ApiTenantUser;
use App\Models\SysAdmin\Role;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Market\Shop
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string|null $company_name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property int|null $address_id
 * @property array $location
 * @property ShopStateEnum $state
 * @property ShopTypeEnum $type
 * @property string|null $open_at
 * @property string|null $closed_at
 * @property int $country_id
 * @property int $language_id
 * @property int $currency_id
 * @property int $timezone_id
 * @property array $data
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property int|null $sender_email_id
 * @property int|null $prospects_sender_email_id
 * @property-read \App\Models\Market\ShopAccountingStats|null $accountingStats
 * @property-read Collection<int, Address> $addresses
 * @property-read ApiTenantUser|null $apiTenantUser
 * @property-read Country $country
 * @property-read \App\Models\Market\ShopCRMStats|null $crmStats
 * @property-read Currency $currency
 * @property-read Collection<int, Customer> $customers
 * @property-read Collection<int, \App\Models\Market\ProductCategory> $departments
 * @property-read Collection<int, FulfilmentOrder> $fulfilmentOrders
 * @property-read \App\Models\Market\ShopFulfilmentStats|null $fulfilmentStats
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Issue> $issues
 * @property-read \App\Models\Market\ShopMailStats|null $mailStats
 * @property-read Collection<int, OfferCampaign> $offerCampaigns
 * @property-read Collection<int, Order> $orders
 * @property-read Organisation $organisation
 * @property-read Collection<int, Outbox> $outboxes
 * @property-read Collection<int, PaymentAccount> $paymentAccounts
 * @property-read Collection<int, PaymentServiceProvider> $paymentServiceProviders
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, \App\Models\Market\Product> $products
 * @property-read Collection<int, Prospect> $prospects
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, SerialReference> $serialReferences
 * @property-read Collection<int, \App\Models\Market\ShippingZoneSchema> $shippingZoneSchemas
 * @property-read \App\Models\Market\ShopStats|null $stats
 * @property-read TaxNumber|null $taxNumber
 * @property-read Timezone $timezone
 * @property-read UniversalSearch|null $universalSearch
 * @property-read Website|null $website
 * @method static \Database\Factories\Market\ShopFactory factory($count = null, $state = [])
 * @method static Builder|Shop newModelQuery()
 * @method static Builder|Shop newQuery()
 * @method static Builder|Shop onlyTrashed()
 * @method static Builder|Shop query()
 * @method static Builder|Shop withTrashed()
 * @method static Builder|Shop withoutTrashed()
 * @mixin Eloquent
 */
class Shop extends Model
{
    use HasAddresses;
    use SoftDeletes;
    use HasSlug;

    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'location' => 'array',
        'type'     => ShopTypeEnum::class,
        'state'    => ShopStateEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
        'location' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(6);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function crmStats(): HasOne
    {
        return $this->hasOne(ShopCRMStats::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ShopStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(ShopAccountingStats::class);
    }

    public function fulfilmentStats(): HasOne
    {
        return $this->hasOne(ShopFulfilmentStats::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function fulfilmentOrders(): HasMany
    {
        return $this->hasMany(FulfilmentOrder::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function website(): HasOne
    {
        return $this->hasOne(Website::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function departments(): MorphMany
    {
        return $this->morphMany(ProductCategory::class, 'parent');
    }

    //    public function families(): HasMany
    //    {
    //        return $this->hasMany(Family::class);
    //    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    public function paymentServiceProviders(): BelongsToMany
    {
        return $this->belongsToMany(PaymentServiceProvider::class)->using(PaymentServiceProviderShop::class)
            ->withTimestamps();
    }
    public function paymentAccounts(): BelongsToMany
    {
        return $this->belongsToMany(PaymentAccount::class)->using(PaymentAccountShop::class)
            ->withTimestamps();
    }

    public function accounts(): PaymentAccount
    {
        return $this->paymentAccounts()->where('type', PaymentAccountTypeEnum::ACCOUNT)->first();
    }

    public function outboxes(): HasMany
    {
        return $this->hasMany(Outbox::class);
    }

    public function offerCampaigns(): HasMany
    {
        return $this->hasMany(OfferCampaign::class);
    }

    public function taxNumber(): MorphOne
    {
        return $this->morphOne(TaxNumber::class, 'owner');
    }

    public function mailStats(): HasOne
    {
        return $this->hasOne(ShopMailStats::class);
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }

    public function shippingZoneSchemas(): HasMany
    {
        return $this->hasMany(ShippingZoneSchema::class);
    }

    public function serialReferences(): MorphMany
    {
        return $this->morphMany(SerialReference::class, 'container');
    }

    public function apiTenantUser(): MorphOne
    {
        return $this->morphOne(ApiTenantUser::class, 'userable');
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'scope');
    }


}
