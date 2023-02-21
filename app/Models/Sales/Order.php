<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 22:46:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Sales;

use App\Models\Delivery\DeliveryNote;
use App\Models\Marketing\Shop;
use App\Models\Traits\HasOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

/**
 * App\Models\Sales\Order
 *
 * @property int $id
 * @property string $slug
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $customer_client_id
 * @property string|null $number
 * @property string|null $customer_number Customers own order number
 * @property string|null $type
 * @property string $state
 * @property bool $is_invoiced
 * @property bool|null $is_picking_on_hold
 * @property bool|null $can_dispatch
 * @property string $items_discounts
 * @property string $items_net
 * @property int $currency_id
 * @property string $exchange
 * @property string $charges
 * @property string|null $shipping
 * @property string $net
 * @property string $tax
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at equivalent deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryNote> $deliveryNotes
 * @property-read int|null $delivery_notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Shop $shop
 * @property-read \App\Models\Sales\OrderStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCanDispatch($value)
 * @method static Builder|Order whereCancelledAt($value)
 * @method static Builder|Order whereCharges($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyId($value)
 * @method static Builder|Order whereCustomerClientId($value)
 * @method static Builder|Order whereCustomerId($value)
 * @method static Builder|Order whereCustomerNumber($value)
 * @method static Builder|Order whereData($value)
 * @method static Builder|Order whereExchange($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereIsInvoiced($value)
 * @method static Builder|Order whereIsPickingOnHold($value)
 * @method static Builder|Order whereItemsDiscounts($value)
 * @method static Builder|Order whereItemsNet($value)
 * @method static Builder|Order whereNet($value)
 * @method static Builder|Order whereNumber($value)
 * @method static Builder|Order whereShipping($value)
 * @method static Builder|Order whereShopId($value)
 * @method static Builder|Order whereSlug($value)
 * @method static Builder|Order whereSourceId($value)
 * @method static Builder|Order whereState($value)
 * @method static Builder|Order whereTax($value)
 * @method static Builder|Order whereType($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasOrder;
    use HasSlug;
    use SoftDeletes;

    const DELETED_AT = 'cancelled_at';


    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deliveryNotes(): MorphToMany
    {
        return $this->morphToMany(DeliveryNote::class, 'delivery_noteable');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrderStats::class);
    }



}

