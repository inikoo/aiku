<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:29:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Marketing;

use App\Actions\Central\Tenant\HydrateTenant;
use App\Enums\Marketing\Shop\ShopStateEnum;
use App\Enums\Marketing\Shop\ShopSubtypeEnum;
use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Helpers\Address;
use App\Models\Leads\Prospect;
use App\Models\Mail\Outbox;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\PaymentAccountShop;
use App\Models\Traits\HasAddress;
use App\Models\Web\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\Shop
 *
 * @property int $id
 * @property string|null $slug
 * @property string $code
 * @property string $name
 * @property string|null $company_name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $url
 * @property string|null $tax_number
 * @property string|null $tax_number_status
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property int|null $address_id
 * @property array $location
 * @property ShopStateEnum $state
 * @property ShopTypeEnum $type
 * @property ShopSubtypeEnum|null $subtype
 * @property string|null $open_at
 * @property string|null $closed_at
 * @property int $language_id
 * @property int $currency_id
 * @property int $timezone_id
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Marketing\ShopAccountingStats|null $accountingStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Customer> $customers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Department> $departments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Family> $families
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FulfilmentOrder> $fulfilmentOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Outbox> $outboxes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentAccount> $paymentAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Product> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Prospect> $prospects
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\Service> $services
 * @property-read \App\Models\Marketing\ShopStats|null $stats
 * @property-read Website|null $website
 * @method static Builder|Shop newModelQuery()
 * @method static Builder|Shop newQuery()
 * @method static Builder|Shop onlyTrashed()
 * @method static Builder|Shop query()
 * @method static Builder|Shop withTrashed()
 * @method static Builder|Shop withoutTrashed()
 * @mixin \Eloquent
 */
class Shop extends Model
{
    use HasAddress;
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'location' => 'array',
        'type'     => ShopTypeEnum::class,
        'subtype'  => ShopSubtypeEnum::class,
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

    protected static function booted()
    {
        static::created(
            function () {
                HydrateTenant::make()->marketingStats();
            }
        );
        static::deleted(
            function () {
                HydrateTenant::make()->marketingStats();
            }
        );

        static::updated(function (Shop $shop) {
            if (!$shop->wasRecentlyCreated) {
                if ($shop->wasChanged(['type', 'subtype', 'state'])) {
                    HydrateTenant::make()->marketingStats();
                }
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
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

    public function fulfilmentOrders(): HasMany
    {
        return $this->hasMany(FulfilmentOrder::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function website(): HasOne
    {
        return $this->hasOne(Website::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentAccounts(): BelongsToMany
    {
        return $this->belongsToMany(PaymentAccount::class)->using(PaymentAccountShop::class)
            ->withTimestamps();
    }

    public function accounts(): PaymentAccount
    {
        return $this->paymentAccounts()->where('payment_accounts.data->service-code', 'accounts')->first();
    }

    public function outboxes(): HasMany
    {
        return $this->hasMany(Outbox::class);
    }
}
