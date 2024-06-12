<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 17:25:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\CollectionCategory;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Subscription;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\Rental;
use App\Models\Goods\TradeUnit;
use App\Models\GroupDropshippingStat;
use App\Models\Helpers\Barcode;
use App\Models\Helpers\Currency;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\Manufacturing\Artefact;
use App\Models\Manufacturing\ManufactureTask;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use App\Models\Ordering\Order;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\Traits\HasImage;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as LaravelCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\Group
 *
 * @property int $id
 * @property string $ulid
 * @property string $slug
 * @property string|null $subdomain
 * @property string $code
 * @property string $name
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $currency_id customer accounting currency
 * @property int|null $image_id
 * @property int $number_organisations
 * @property string|null $dropshipping_integration_token
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\GroupAccountingStats|null $accountingStats
 * @property-read LaravelCollection<int, Agent> $agents
 * @property-read LaravelCollection<int, Artefact> $artefacts
 * @property-read LaravelCollection<int, Asset> $assets
 * @property-read LaravelCollection<int, Barcode> $barcodes
 * @property-read \App\Models\SysAdmin\GroupCatalogueStats|null $catalogueStats
 * @property-read LaravelCollection<int, CustomerClient> $clients
 * @property-read LaravelCollection<int, ClockingMachine> $clockingMachines
 * @property-read LaravelCollection<int, CollectionCategory> $collectionCategories
 * @property-read LaravelCollection<int, Collection> $collections
 * @property-read \App\Models\SysAdmin\GroupCRMStats|null $crmStats
 * @property-read Currency $currency
 * @property-read LaravelCollection<int, Customer> $customers
 * @property-read LaravelCollection<int, DropshippingCustomerPortfolio> $dropshippingCustomerPortfolios
 * @property-read GroupDropshippingStat|null $dropshippingStats
 * @property-read LaravelCollection<int, Employee> $employees
 * @property-read \App\Models\SysAdmin\GroupFulfilmentStats|null $fulfilmentStats
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\GroupJobPosition> $groupJobPositions
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\Guest> $guests
 * @property-read \App\Models\SysAdmin\GroupHumanResourcesStats|null $humanResourcesStats
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read \App\Models\SysAdmin\GroupInventoryStats|null $inventoryStats
 * @property-read LaravelCollection<int, Invoice> $invoices
 * @property-read LaravelCollection<int, JobPosition> $jobPositions
 * @property-read LaravelCollection<int, Location> $locations
 * @property-read \App\Models\SysAdmin\GroupMailStats|null $mailStats
 * @property-read \App\Models\SysAdmin\GroupMailshotsIntervals|null $mailshotsIntervals
 * @property-read \App\Models\SysAdmin\GroupManufactureStats|null $manufactureStats
 * @property-read LaravelCollection<int, ManufactureTask> $manufactureTasks
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read LaravelCollection<int, Order> $orders
 * @property-read \App\Models\SysAdmin\GroupOrdersIntervals|null $ordersIntervals
 * @property-read LaravelCollection<int, OrgPaymentServiceProvider> $orgPaymentServiceProviders
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\Organisation> $organisations
 * @property-read LaravelCollection<int, Outbox> $outboxes
 * @property-read LaravelCollection<int, PaymentAccount> $paymentAccounts
 * @property-read LaravelCollection<int, PaymentServiceProvider> $paymentServiceProviders
 * @property-read LaravelCollection<int, Payment> $payments
 * @property-read LaravelCollection<int, PostRoom> $postRooms
 * @property-read LaravelCollection<int, ProductCategory> $productCategories
 * @property-read LaravelCollection<int, Production> $productions
 * @property-read LaravelCollection<int, Product> $products
 * @property-read LaravelCollection<int, PurchaseOrder> $purchaseOrders
 * @property-read LaravelCollection<int, RawMaterial> $rawMaterials
 * @property-read LaravelCollection<int, RecurringBill> $recurringBills
 * @property-read LaravelCollection<int, Rental> $rentals
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\Role> $roles
 * @property-read \App\Models\SysAdmin\GroupSalesIntervals|null $salesIntervals
 * @property-read \App\Models\SysAdmin\GroupSalesStats|null $salesStats
 * @property-read LaravelCollection<int, Service> $services
 * @property-read LaravelCollection<int, StockFamily> $stockFamilies
 * @property-read LaravelCollection<int, Stock> $stocks
 * @property-read LaravelCollection<int, Subscription> $subscriptions
 * @property-read LaravelCollection<int, SupplierProduct> $supplierProducts
 * @property-read LaravelCollection<int, Supplier> $suppliers
 * @property-read \App\Models\SysAdmin\GroupSupplyChainStats|null $supplyChainStats
 * @property-read \App\Models\SysAdmin\GroupSysAdminStats|null $sysadminStats
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\Task> $tasks
 * @property-read LaravelCollection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read LaravelCollection<int, TradeUnit> $tradeUnits
 * @property-read LaravelCollection<int, \App\Models\SysAdmin\User> $users
 * @property-read LaravelCollection<int, WarehouseArea> $warehouseAreas
 * @property-read LaravelCollection<int, Warehouse> $warehouses
 * @property-read \App\Models\SysAdmin\GroupWebStats|null $webStats
 * @property-read LaravelCollection<int, WebUser> $webUsers
 * @property-read LaravelCollection<int, Webpage> $webpages
 * @property-read LaravelCollection<int, Website> $websites
 * @method static \Database\Factories\SysAdmin\GroupFactory factory($count = null, $state = [])
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static Builder|Group query()
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin Eloquent
 */
class Group extends Authenticatable implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;
    use InteractsWithMedia;
    use HasImage;
    use HasApiTokens;

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

    public function tradeUnits(): HasMany
    {
        return $this->hasMany(TradeUnit::class);
    }
    public function stockFamilies(): HasMany
    {
        return $this->hasMany(StockFamily::class);
    }
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function humanResourcesStats(): HasOne
    {
        return $this->hasOne(GroupHumanResourcesStats::class);
    }

    public function crmStats(): HasOne
    {
        return $this->hasOne(GroupCRMStats::class);
    }

    public function accountingStats(): HasOne
    {
        return $this->hasOne(GroupAccountingStats::class);
    }

    public function salesStats(): HasOne
    {
        return $this->hasOne(GroupSalesStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(GroupSalesIntervals::class);
    }

    public function ordersIntervals(): HasOne
    {
        return $this->hasOne(GroupOrdersIntervals::class);
    }

    public function mailshotsIntervals(): HasOne
    {
        return $this->hasOne(GroupMailshotsIntervals::class);
    }

    public function sysadminStats(): HasOne
    {
        return $this->hasOne(GroupSysAdminStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(GroupInventoryStats::class);
    }

    public function supplyChainStats(): HasOne
    {
        return $this->hasOne(GroupSupplyChainStats::class);
    }

    public function webStats(): HasOne
    {
        return $this->hasOne(GroupWebStats::class);
    }

    public function mailStats(): HasOne
    {
        return $this->hasOne(GroupMailStats::class);
    }

    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    public function postRooms(): HasMany
    {
        return $this->hasMany(PostRoom::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function webUsers(): HasMany
    {
        return $this->hasMany(WebUser::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }

    public function groupJobPositions(): HasMany
    {
        return $this->hasMany(GroupJobPosition::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function fulfilmentStats(): HasOne
    {
        return $this->hasOne(GroupFulfilmentStats::class);
    }

    public function manufactureStats(): HasOne
    {
        return $this->hasOne(GroupManufactureStats::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentServiceProviders(): HasMany
    {
        return $this->hasMany(PaymentServiceProvider::class);
    }

    public function orgPaymentServiceProviders(): HasMany
    {
        return $this->hasMany(OrgPaymentServiceProvider::class);
    }

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function catalogueStats(): HasOne
    {
        return $this->hasOne(GroupCatalogueStats::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function collectionCategories(): HasMany
    {
        return $this->hasMany(CollectionCategory::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function warehouseAreas(): HasMany
    {
        return $this->hasMany(WarehouseArea::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function manufactureTasks(): HasMany
    {
        return $this->hasMany(ManufactureTask::class);
    }

    public function artefacts(): HasMany
    {
        return $this->hasMany(Artefact::class);
    }

    public function clockingMachines(): HasMany
    {
        return $this->hasMany(ClockingMachine::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class);
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

    public function families(): ?LaravelCollection
    {
        return $this->productCategories()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function dropshippingStats(): HasOne
    {
        return $this->hasOne(GroupDropshippingStat::class);
    }

    public function dropshippingCustomerPortfolios(): HasMany
    {
        return $this->hasMany(DropshippingCustomerPortfolio::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(CustomerClient::class);
    }

    public function outboxes(): HasMany
    {
        return $this->hasMany(Outbox::class);
    }

}
