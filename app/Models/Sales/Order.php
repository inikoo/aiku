<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 22:46:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Sales;

use App\Enums\Sales\Order\OrderStateEnum;
use App\Enums\Sales\Order\OrderStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Marketing\Shop;
use App\Models\Traits\HasOrder;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
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
 * @property OrderStateEnum $state
 * @property OrderStatusEnum $status
 * @property string $date
 * @property string|null $submitted_at
 * @property string|null $in_warehouse_at
 * @property string|null $packed_at
 * @property string|null $finalised_at
 * @property string|null $dispatched_at
 * @property string|null $cancelled_at
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryNote> $deliveryNotes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read Shop $shop
 * @property-read \App\Models\Sales\OrderStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sales\Transaction> $transactions
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order query()
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use UsesTenantConnection;
    use HasOrder;
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;

    protected $casts = [
        'data'   => 'array',
        'state'  => OrderStateEnum::class,
        'status' => OrderStatusEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deliveryNotes(): MorphToMany
    {
        return $this->morphToMany(DeliveryNote::class, 'delivery_noteable')->withTimestamps();
    }

    public function payments(): MorphToMany
    {
        return $this->morphToMany(Payment::class, 'paymentable')->withTimestamps()->withPivot(['amount','share']);
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
