<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:11:16 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\Proforma\ProformaTypeEnum;
use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Accounting\Invoice
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $number
 * @property int $fulfilment_id
 * @property int $fulfilment_customer_id
 * @property int|null $order_id
 * @property ProformaTypeEnum $type
 * @property int $currency_id
 * @property string $group_exchange
 * @property string $org_exchange
 * @property string $net
 * @property string $total
 * @property string $payment
 * @property string $group_net_amount
 * @property string $org_net_amount
 * @property array|null $paid_at
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Currency $currency
 * @property-read Shop $fulfilment
 * @property-read FulfilmentCustomer $fulfilmentCustomer
 * @property-read Order|null $order
 * @property-read Collection<int, Order> $orders
 * @property-read Collection<int, Pallet> $pallets
 * @property-write mixed $exchange
 * @property-read UniversalSearch|null $universalSearch
 * @method static Builder|FulfilmentProforma newModelQuery()
 * @method static Builder|FulfilmentProforma newQuery()
 * @method static Builder|FulfilmentProforma onlyTrashed()
 * @method static Builder|FulfilmentProforma query()
 * @method static Builder|FulfilmentProforma withTrashed()
 * @method static Builder|FulfilmentProforma withoutTrashed()
 * @mixin \Eloquent
 */
class FulfilmentProforma extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasAddresses;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'type'    => ProformaTypeEnum::class,
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
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /*    protected static function booted()
        {
            static::deleted(
                function (Invoice $invoice) {
                    CustomerHydrateInvoices::dispatch($invoice->customer);
                    ShopHydrateInvoices::dispatch($invoice->shop);
                }
            );
        }*/

    protected $guarded = [];

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function fulfilment(): BelongsTo
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class);
    }


    /** @noinspection PhpUnused */
    public function setExchangeAttribute($val)
    {
        $this->attributes['exchange'] = sprintf('%.6f', $val);
    }

    /*    public function stats(): HasOne
        {
            return $this->hasOne(InvoiceStats::class);
        }*/

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
