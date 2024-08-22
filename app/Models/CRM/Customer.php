<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:21:10 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\Accounting\CreditTransaction;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\TopUp;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\Platform;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Helpers\Address;
use App\Models\Helpers\Issue;
use App\Models\Helpers\Media;
use App\Models\Helpers\TaxNumber;
use App\Models\Helpers\UniversalSearch;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Models\SupplyChain\Stock;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasEmail;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
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
use OwenIt\Auditing\Contracts\Auditable;
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
 * @property int|null $address_id
 * @property array $location
 * @property int|null $delivery_address_id
 * @property CustomerStatusEnum $status
 * @property CustomerStateEnum $state
 * @property CustomerTradeStateEnum $trade_state number of invoices
 * @property bool $is_fulfilment
 * @property bool $is_dropshipping
 * @property Carbon|null $last_submitted_order_at
 * @property Carbon|null $last_dispatched_delivery_at
 * @property Carbon|null $last_invoiced_at
 * @property array $data
 * @property array $settings
 * @property string|null $internal_notes
 * @property string|null $warehouse_notes
 * @property int|null $prospects_sender_email_id
 * @property int|null $image_id
 * @property Carbon|null $fetched_at
 * @property Carbon|null $last_fetched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property array $migration_data
 * @property string $balance
 * @property-read Address|null $address
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, \App\Models\CRM\Appointment> $appointments
 * @property-read MediaCollection<int, Media> $attachments
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Collection<int, CustomerClient> $clients
 * @property-read Collection<int, CreditTransaction> $creditTransactions
 * @property-read Address|null $deliveryAddress
 * @property-read \App\Models\CRM\CustomerDropshippingStat|null $dropshippingStats
 * @property-read FulfilmentCustomer|null $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Media|null $image
 * @property-read MediaCollection<int, Media> $images
 * @property-read Collection<int, Invoice> $invoices
 * @property-read Collection<int, Issue> $issues
 * @property-read MediaCollection<int, Media> $media
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read Collection<int, Order> $orders
 * @property-read Organisation $organisation
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Platform> $platforms
 * @property-read Collection<int, Portfolio> $portfolios
 * @property-read Collection<int, Asset> $products
 * @property-read Shop|null $shop
 * @property-read ShopifyUser|null $shopifyUser
 * @property-read \App\Models\CRM\CustomerStats|null $stats
 * @property-read Collection<int, Stock> $stocks
 * @property-read Collection<int, StoredItem> $storedItems
 * @property-read TaxNumber|null $taxNumber
 * @property-read Collection<int, TopUp> $topUps
 * @property-read Collection<int, Transaction> $transactions
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
class Customer extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasAddress;
    use HasAddresses;
    use HasSlug;
    use HasUniversalSearch;
    use HasImage;
    use HasFactory;
    use HasHistory;
    use InShop;
    use HasAttachments;
    use HasEmail;

    protected $casts = [
        'data'                        => 'array',
        'settings'                    => 'array',
        'location'                    => 'array',
        'migration_data'              => 'array',
        'state'                       => CustomerStateEnum::class,
        'status'                      => CustomerStatusEnum::class,
        'trade_state'                 => CustomerTradeStateEnum::class,
        'last_submitted_order_at'     => 'datetime',
        'last_dispatched_delivery_at' => 'datetime',
        'last_invoiced_at'            => 'datetime',
        'fetched_at'                  => 'datetime',
        'last_fetched_at'             => 'datetime',
    ];


    protected $attributes = [
        'data'           => '{}',
        'settings'       => '{}',
        'location'       => '{}',
        'migration_data' => '{}'
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        $tags = ['crm'];
        if ($this->is_fulfilment) {
            $tags[] = 'fulfilment';
        }

        return $tags;
    }

    protected array $auditInclude = [
        'reference',
        'contact_name',
        'company_name',
        'email',
        'phone',
        'contact_website',
        'identity_document_type',
        'identity_document_number',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->company_name;
                if ($slug == '') {
                    $slug = $this->contact_name;
                }
                if ($slug == '' or $slug == 'Unknown') {
                    $slug = $this->reference;
                }

                return $slug;
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(36)
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
                $name = $customer->company_name == '' ? $customer->contact_name : $customer->company_name;
                $name = trim($name);
                if ($name == '') {
                    $emailData = explode('@', $customer->email);
                    $name      = $emailData[0] ?? $customer->email;
                }
                $customer->name = $name;
            }
        );

        static::updated(function (Customer $customer) {
            if ($customer->wasChanged('trade_state')) {
                ShopHydrateCustomerInvoices::dispatch($customer->shop);
            }
            if ($customer->wasChanged(['contact_name', 'company_name', 'email'])) {
                $name = $customer->company_name == '' ? $customer->contact_name : $customer->company_name;
                $name = trim($name);
                if ($name == '') {
                    $emailData = explode('@', $customer->email);
                    $name      = $emailData[0] ?? $customer->email;
                }

                $customer->updateQuietly(
                    [
                        'name' => $name
                    ]
                );
            }
        });
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
        return $this->morphMany(Asset::class, 'owner', 'owner_type', 'owner_id', 'id');
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner', 'owner_type', 'owner_id', 'id');
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


    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function hasUsers(): bool
    {
        return (bool)$this->webUsers->count();
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function dropshippingStats(): HasOne
    {
        return $this->hasOne(CustomerDropshippingStat::class);
    }


    public function platforms(): MorphToMany
    {
        return $this->morphToMany(Platform::class, 'model', 'model_has_platforms')
            ->withPivot('group_id', 'organisation_id', 'shop_id', 'reference')->withTimestamps();
    }

    public function platform(): Platform|null
    {
        /** @var Platform $platform */
        $platform = $this->platforms()->first();

        return $platform;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function topUps(): HasMany
    {
        return $this->hasMany(TopUp::class);
    }

    public function shopifyUser(): HasOne
    {
        return $this->hasOne(ShopifyUser::class);
    }
}
