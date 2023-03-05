<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:53:31 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;

use App\Actions\Marketing\Shop\HydrateShop;
use App\Models\Accounting\Payment;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Helpers\Address;
use App\Models\Inventory\Stock;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Traits\HasAddress;
use App\Models\Web\WebUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
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
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomerClient> $clients
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FulfilmentOrder> $fulfilmentOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Invoice> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read Shop|null $shop
 * @property-read \App\Models\Sales\CustomerStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WebUser> $webUsers
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer onlyTrashed()
 * @method static Builder|Customer query()
 * @method static Builder|Customer withTrashed()
 * @method static Builder|Customer withoutTrashed()
 * @mixin \Eloquent
 */
class Customer extends Model
{
    use UsesTenantConnection;
    use SoftDeletes;
    use HasAddress;
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
                $customer->name = $customer->company_name == '' ? $customer->contact_name : $customer->company_name;
            }
        );

        static::created(
            function (Customer $customer) {
                HydrateShop::make()->customerStats($customer->shop);
            }
        );

        static::updated(function (Customer $customer) {
            if ($customer->wasChanged('trade_state')) {
                HydrateShop::make()->customerNumberInvoicesStats($customer->shop);
            }
            if ($customer->wasChanged(['company_name', 'contact_name'])) {
                $customer->name = $customer->company_name == '' ? $customer->contact_name : $customer->company_name;
            }
        });
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
