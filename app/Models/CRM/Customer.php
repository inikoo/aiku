<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:21:10 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\StoredItem;
use App\Models\Helpers\Address;
use App\Models\Helpers\Issue;
use App\Models\Helpers\TaxNumber;
use App\Models\Market\Product;
use App\Models\Market\Shop;
use App\Models\Media\Media;
use App\Models\OMS\Order;
use App\Models\Search\UniversalSearch;
use App\Models\SupplyChain\Stock;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasPhoto;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\CRM\Customer
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $image_id
 * @property string $slug
 * @property string|null $reference customer public id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property string|null $contact_website
 * @property array $location
 * @property CustomerStatusEnum $status
 * @property CustomerStateEnum $state
 * @property CustomerTradeStateEnum $trade_state number of invoices
 * @property bool $is_fulfilment
 * @property bool $is_dropshipping
 * @property array $data
 * @property mixed $settings
 * @property string|null $internal_notes
 * @property string|null $warehouse_notes
 * @property int|null $prospects_sender_email_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, \App\Models\CRM\Appointment> $appointments
 * @property-read Collection<int, CustomerClient> $clients
 * @property-read FulfilmentCustomer|null $fulfilmentCustomer
 * @property-read Collection<int, FulfilmentOrder> $fulfilmentOrders
 * @property-read Group $group
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Issue> $issues
 * @property-read MediaCollection<int, Media> $media
 * @property-read Collection<int, Order> $orders
 * @property-read Organisation $organisation
 * @property-read Collection<int, PalletDelivery> $palletDeliveries
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Product> $products
 * @property-read Shop|null $shop
 * @property-read \App\Models\CRM\CustomerStats|null $stats
 * @property-read Collection<int, Stock> $stocks
 * @property-read Collection<int, StoredItem> $storedItems
 * @property-read TaxNumber|null $taxNumber
 * @property-read UniversalSearch|null $universalSearch
 * @property-read Collection<int, \App\Models\CRM\WebUser> $webUsers
 * @method static \Database\Factories\CRM\CustomerFactory factory($count = null, $state = [])
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer onlyTrashed()
 * @method static Builder|Customer query()
 * @method static Builder|Customer withTrashed()
 * @method static Builder|Customer withoutTrashed()
 * @mixin \Eloquent
 */
class Customer extends Model implements HasMedia
{
    use SoftDeletes;
    use HasAddresses;
    use HasSlug;
    use HasUniversalSearch;
    use HasPhoto;
    use HasFactory;

    protected $casts = [
        'data'        => 'array',
        'settings'    => 'settings',
        'location'    => 'array',
        'state'       => CustomerStateEnum::class,
        'status'      => CustomerStatusEnum::class,
        'trade_state' => CustomerTradeStateEnum::class

    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
        'location' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {

                $slug = $this->company_name;
                if ($slug == '') {
                    $slug = $this->contact_name;
                }
                if ($slug == '' or $slug=='Unknown') {
                    $slug = $this->reference;
                }

                return $slug;
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(32)
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(
            function (Customer $customer) {
                $customer->name = $customer->company_name == '' ? $customer->contact_name : $customer->company_name;
            }
        );

        static::updated(function (Customer $customer) {
            if ($customer->wasChanged('trade_state')) {
                ShopHydrateCustomerInvoices::dispatch($customer->shop);
            }
            if ($customer->wasChanged(['contact_name', 'company_name'])) {
                $customer->updateQuietly(
                    [
                        'name' => $customer->company_name == '' ? $customer->contact_name : $customer->company_name
                    ]
                );
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(CustomerClient::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(CustomerStats::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function webUsers(): HasMany
    {
        return $this->hasMany(WebUser::class);
    }


    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'owner', 'owner_type', 'owner_id', 'id');
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner', 'owner_type', 'owner_id', 'id');
    }

    public function fulfilmentOrders(): HasMany
    {
        return $this->hasMany(FulfilmentOrder::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function storedItems(): HasMany
    {
        return $this->hasMany(StoredItem::class, 'fulfilment_customer_id');
    }

    public function taxNumber(): MorphOne
    {
        return $this->morphOne(TaxNumber::class, 'owner');
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }

    public function fulfilmentCustomer(): HasOne
    {
        return $this->hasOne(FulfilmentCustomer::class);
    }

    public function palletDeliveries(): HasMany
    {
        return $this->hasMany(PalletDelivery::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function hasUsers(): bool
    {
        return (bool)$this->webUsers->count();
    }
}
