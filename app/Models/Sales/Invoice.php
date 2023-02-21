<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:27:31 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Sales;


use App\Actions\Marketing\Shop\HydrateShop;
use App\Actions\Sales\Customer\HydrateCustomer;
use App\Models\Marketing\Shop;
use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Sales\Invoice
 *
 * @property int $id
 * @property string $slug
 * @property string $number
 * @property int $shop_id
 * @property int $customer_id
 * @property int $order_id
 * @property string $type
 * @property int $currency_id
 * @property string $exchange
 * @property string $net
 * @property string $total
 * @property string $payment
 * @property array|null $paid_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\InvoiceTransaction> $invoiceTransactions
 * @property-read int|null $invoice_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Order> $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Shop $shop
 * @property-read \App\Models\Sales\InvoiceStats|null $stats
 * @method static Builder|Invoice newModelQuery()
 * @method static Builder|Invoice newQuery()
 * @method static Builder|Invoice onlyTrashed()
 * @method static Builder|Invoice query()
 * @method static Builder|Invoice whereCreatedAt($value)
 * @method static Builder|Invoice whereCurrencyId($value)
 * @method static Builder|Invoice whereCustomerId($value)
 * @method static Builder|Invoice whereData($value)
 * @method static Builder|Invoice whereDeletedAt($value)
 * @method static Builder|Invoice whereExchange($value)
 * @method static Builder|Invoice whereId($value)
 * @method static Builder|Invoice whereNet($value)
 * @method static Builder|Invoice whereNumber($value)
 * @method static Builder|Invoice whereOrderId($value)
 * @method static Builder|Invoice wherePaidAt($value)
 * @method static Builder|Invoice wherePayment($value)
 * @method static Builder|Invoice whereShopId($value)
 * @method static Builder|Invoice whereSlug($value)
 * @method static Builder|Invoice whereSourceId($value)
 * @method static Builder|Invoice whereTotal($value)
 * @method static Builder|Invoice whereType($value)
 * @method static Builder|Invoice whereUpdatedAt($value)
 * @method static Builder|Invoice withTrashed()
 * @method static Builder|Invoice withoutTrashed()
 * @mixin \Eloquent
 */
class Invoice extends Model
{

    use SoftDeletes;
    use HasSlug;
    use HasAddress;

    protected $casts = [
        'data'    => 'array',
        'paid_at' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->saveSlugsTo('slug');
    }

    protected static function booted()
    {
        static::created(
            function (Invoice $invoice) {
                HydrateCustomer::make()->invoices($invoice->customer);
                HydrateShop::make()->invoices($invoice->shop);

            }
        );
        static::deleted(
            function (Invoice $invoice) {
                HydrateCustomer::make()->invoices($invoice->customer);
                HydrateShop::make()->invoices($invoice->shop);

            }
        );
    }

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    /**
     * Relation to main order, usually the only one, used no avoid looping over orders
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     */
    public function order(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function invoiceTransactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
    }


    /** @noinspection PhpUnused */
    public function setExchangeAttribute($val)
    {
        $this->attributes['exchange'] = sprintf('%.6f', $val);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(InvoiceStats::class);
    }
}
