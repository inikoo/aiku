<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:29:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Catalogue;

use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\OrgPaymentServiceProviderShop;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Catalogue\Shop\ShopMailshotsIntervals;
use App\Models\Catalogue\Shop\ShopOrdersIntervals;
use App\Models\CRM\Appointment;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Deals\OfferCampaign;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\Helpers\Currency;
use App\Models\Helpers\Issue;
use App\Models\Helpers\SerialReference;
use App\Models\Helpers\TaxNumber;
use App\Models\Helpers\Timezone;
use App\Models\Helpers\UniversalSearch;
use App\Models\Mail\Outbox;
use App\Models\Mail\SenderEmail;
use App\Models\Ordering\Order;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\ShopDropshippingStat;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\Task;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InOrganisation;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as LaravelCollection;
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
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\Shop
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
 * @property int|null $collection_address_id
 * @property ShopStateEnum $state
 * @property ShopTypeEnum $type
 * @property string|null $open_at
 * @property string|null $closed_at
 * @property int $country_id
 * @property int $language_id
 * @property int $currency_id
 * @property int $timezone_id
 * @property int|null $image_id
 * @property array $data
 * @property array $settings
 * @property int|null $sender_email_id
 * @property int|null $prospects_sender_email_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read \App\Models\Catalogue\ShopAccountingStats|null $accountingStats
 * @property-read Address|null $address
 * @property-read LaravelCollection<int, Address> $addresses
 * @property-read LaravelCollection<int, Appointment> $appointments
 * @property-read LaravelCollection<int, \App\Models\Catalogue\Asset> $assets
 * @property-read LaravelCollection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Address|null $collectionAddress
 * @property-read LaravelCollection<int, \App\Models\Catalogue\CollectionCategory> $collectionCategories
 * @property-read LaravelCollection<int, \App\Models\Catalogue\Collection> $collections
 * @property-read Country $country
 * @property-read \App\Models\Catalogue\ShopCRMStats|null $crmStats
 * @property-read Currency $currency
 * @property-read LaravelCollection<int, Customer> $customers
 * @property-read ShopDropshippingStat|null $dropshippingStats
 * @property-read Fulfilment|null $fulfilment
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read LaravelCollection<int, Invoice> $invoices
 * @property-read LaravelCollection<int, Issue> $issues
 * @property-read \App\Models\Catalogue\ShopMailStats|null $mailStats
 * @property-read ShopMailshotsIntervals|null $mailshotsIntervals
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read LaravelCollection<int, OfferCampaign> $offerCampaigns
 * @property-read ShopOrdersIntervals|null $orderIntervals
 * @property-read LaravelCollection<int, Order> $orders
 * @property-read LaravelCollection<int, OrgPaymentServiceProvider> $orgPaymentServiceProviders
 * @property-read Organisation $organisation
 * @property-read LaravelCollection<int, Outbox> $outboxes
 * @property-read LaravelCollection<int, PaymentAccount> $paymentAccounts
 * @property-read LaravelCollection<int, Payment> $payments
 * @property-read LaravelCollection<int, \App\Models\Catalogue\ProductCategory> $productCategories
 * @property-read LaravelCollection<int, \App\Models\Catalogue\Product> $products
 * @property-read LaravelCollection<int, Prospect> $prospects
 * @property-read LaravelCollection<int, Rental> $rentals
 * @property-read LaravelCollection<int, Role> $roles
 * @property-read \App\Models\Catalogue\ShopSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\ShopSalesStats|null $salesStats
 * @property-read SenderEmail|null $senderEmail
 * @property-read LaravelCollection<int, SerialReference> $serialReferences
 * @property-read LaravelCollection<int, \App\Models\Catalogue\Service> $services
 * @property-read LaravelCollection<int, ShippingZoneSchema> $shippingZoneSchemas
 * @property-read \App\Models\Catalogue\ShopStats|null $stats
 * @property-read LaravelCollection<int, Task> $tasks
 * @property-read TaxNumber|null $taxNumber
 * @property-read Timezone $timezone
 * @property-read UniversalSearch|null $universalSearch
 * @property-read Website|null $website
 * @method static \Database\Factories\Catalogue\ShopFactory factory($count = null, $state = [])
 * @method static Builder|Shop newModelQuery()
 * @method static Builder|Shop newQuery()
 * @method static Builder|Shop onlyTrashed()
 * @method static Builder|Shop query()
 * @method static Builder|Shop withTrashed()
 * @method static Builder|Shop withoutTrashed()
 * @mixin Eloquent
 */
class Shop extends Model implements HasMedia, Auditable
{
    use HasAddress;
    use HasAddresses;
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;
    use InOrganisation;
    use HasHistory;
    use HasImage;

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

    public function crmStats(): HasOne
    {
        return $this->hasOne(ShopCRMStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(ShopSalesStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ShopSalesIntervals::class);
    }

    public function orderIntervals(): HasOne
    {
        return $this->hasOne(ShopOrdersIntervals::class);
    }

    public function mailshotsIntervals(): HasOne
    {
        return $this->hasOne(ShopMailshotsIntervals::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ShopStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(ShopAccountingStats::class);
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

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function departments(): LaravelCollection
    {
        return $this->productCategories()->where('type', ProductCategoryTypeEnum::DEPARTMENT)->get();
    }

    public function subDepartments(): LaravelCollection
    {
        return $this->productCategories()->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)->get();
    }

    public function families(): LaravelCollection
    {
        return $this->productCategories()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }

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

    public function orgPaymentServiceProviders(): BelongsToMany
    {
        return $this->belongsToMany(OrgPaymentServiceProvider::class)->using(OrgPaymentServiceProviderShop::class)
            ->withTimestamps();
    }

    public function paymentAccounts(): BelongsToMany
    {
        return $this->belongsToMany(PaymentAccount::class)->using(PaymentAccountShop::class)
            ->withTimestamps();
    }

    public function accounts(): PaymentAccount
    {
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount= $this->paymentAccounts()->where('shop_id', $this->id)->where('type', PaymentAccountTypeEnum::ACCOUNT)->first();
        return $paymentAccount;
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

    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'scope');
    }

    public function fulfilment(): HasOne
    {
        return $this->hasOne(Fulfilment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function senderEmail(): BelongsTo
    {
        return $this->belongsTo(SenderEmail::class);
    }

    public function collectionCategories(): HasMany
    {
        return $this->hasMany(CollectionCategory::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function collectionAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'collection_address_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'assigner');
    }

    public function dropshippingStats(): HasOne
    {
        return $this->hasOne(ShopDropshippingStat::class);
    }

    public function dropshippingCustomerPortfolios(): HasMany
    {
        return $this->hasMany(DropshippingCustomerPortfolio::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(CustomerClient::class);
    }
}
