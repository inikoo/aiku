<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:53:31 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;


use App\Actions\Marketing\Shop\HydrateShop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Helpers\Address;
use App\Models\Inventory\Stock;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Web\WebUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Sales\Customer
 *
 * @property int $id
 * @property int|null $shop_id
 * @property string $slug
 * @property string $reference customer public id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_number
 * @property string|null $website
 * @property string|null $tax_number
 * @property string|null $tax_number_status
 * @property array $tax_number_data
 * @property array $location
 * @property string $status
 * @property string|null $state
 * @property string|null $trade_state number of invoices
 * @property int|null $billing_address_id
 * @property int|null $delivery_address_id null for customers in fulfilment shops
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @property-read Address|null $billingAddress
 * @property-read \Illuminate\Database\Eloquent\Collection|CustomerClient[] $clients
 * @property-read int|null $clients_count
 * @property-read Address|null $deliveryAddress
 * @property-read \Illuminate\Database\Eloquent\Collection|FulfilmentOrder[] $fulfilmentOrders
 * @property-read int|null $fulfilment_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sales\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sales\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Shop|null $shop
 * @property-read \App\Models\Sales\CustomerStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection|Stock[] $stocks
 * @property-read int|null $stocks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|WebUser[] $webUsers
 * @property-read int|null $web_users_count
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static \Illuminate\Database\Query\Builder|Customer onlyTrashed()
 * @method static Builder|Customer query()
 * @method static Builder|Customer whereBillingAddressId($value)
 * @method static Builder|Customer whereCompanyName($value)
 * @method static Builder|Customer whereContactName($value)
 * @method static Builder|Customer whereCreatedAt($value)
 * @method static Builder|Customer whereData($value)
 * @method static Builder|Customer whereDeletedAt($value)
 * @method static Builder|Customer whereDeliveryAddressId($value)
 * @method static Builder|Customer whereEmail($value)
 * @method static Builder|Customer whereId($value)
 * @method static Builder|Customer whereIdentityDocumentNumber($value)
 * @method static Builder|Customer whereLocation($value)
 * @method static Builder|Customer whereName($value)
 * @method static Builder|Customer wherePhone($value)
 * @method static Builder|Customer whereReference($value)
 * @method static Builder|Customer whereShopId($value)
 * @method static Builder|Customer whereSlug($value)
 * @method static Builder|Customer whereSourceId($value)
 * @method static Builder|Customer whereState($value)
 * @method static Builder|Customer whereStatus($value)
 * @method static Builder|Customer whereTaxNumber($value)
 * @method static Builder|Customer whereTaxNumberData($value)
 * @method static Builder|Customer whereTaxNumberStatus($value)
 * @method static Builder|Customer whereTradeState($value)
 * @method static Builder|Customer whereUpdatedAt($value)
 * @method static Builder|Customer whereWebsite($value)
 * @method static \Illuminate\Database\Query\Builder|Customer withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Customer withoutTrashed()
 * @mixin \Eloquent
 */
class Customer extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data'            => 'array',
        'tax_number_data' => 'array',
        'location'        => 'array',

    ];

    protected $attributes = [
        'data'            => '{}',
        'location'        => '{}',
        'tax_number_data' => '{}',

    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->saveSlugsTo('slug');
    }

    protected static function booted()
    {
        static::creating(
            function (Customer $customer) {
                $customer->name=$customer->company_name==''?$customer->contact_name:$customer->company_name;
            }
        );

        static::created(
            function (Customer $customer) {
                HydrateShop::make()->customerStats($customer->shop);
            }
        );
        static::deleted(
            function (Customer $customer) {
                HydrateShop::make()->customerStats($customer->shop);
            }
        );

        static::updated(function (Customer $customer) {
            if ($customer->wasChanged('trade_state')) {
                HydrateShop::make()->customerNumberInvoicesStats($customer->shop);
            }
            if ($customer->wasChanged(['company_name','contact_name'])) {
                $customer->name=$customer->company_name==''?$customer->contact_name:$customer->company_name;
            }
        });
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
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

    public function getRouteKeyName(): string
    {
        return 'slug';
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


}
