<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:40:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Delivery;

use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Delivery\DeliveryNote
 *
 * @property int $id
 * @property string $slug
 * @property int $shop_id
 * @property int $customer_id
 * @property string $number
 * @property string $type
 * @property string $state
 * @property bool|null $can_dispatch
 * @property int|null $shipper_id
 * @property string|null $weight
 * @property int $number_stocks
 * @property int $number_picks
 * @property int|null $picker_id Main picker
 * @property int|null $packer_id Main packer
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $assigned_at
 * @property \Illuminate\Support\Carbon|null $picking_at
 * @property \Illuminate\Support\Carbon|null $picked_at
 * @property \Illuminate\Support\Carbon|null $packing_at
 * @property \Illuminate\Support\Carbon|null $packed_at
 * @property string|null $finalised_at
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at equivalent to deleted_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Shop $shop
 * @property-read \App\Models\Delivery\DeliveryNoteStats|null $stats
 * @method static Builder|DeliveryNote newModelQuery()
 * @method static Builder|DeliveryNote newQuery()
 * @method static \Illuminate\Database\Query\Builder|DeliveryNote onlyTrashed()
 * @method static Builder|DeliveryNote query()
 * @method static Builder|DeliveryNote whereAssignedAt($value)
 * @method static Builder|DeliveryNote whereCanDispatch($value)
 * @method static Builder|DeliveryNote whereCancelledAt($value)
 * @method static Builder|DeliveryNote whereCreatedAt($value)
 * @method static Builder|DeliveryNote whereCustomerId($value)
 * @method static Builder|DeliveryNote whereData($value)
 * @method static Builder|DeliveryNote whereDate($value)
 * @method static Builder|DeliveryNote whereDeliveryAddressId($value)
 * @method static Builder|DeliveryNote whereDispatchedAt($value)
 * @method static Builder|DeliveryNote whereFinalisedAt($value)
 * @method static Builder|DeliveryNote whereId($value)
 * @method static Builder|DeliveryNote whereNumber($value)
 * @method static Builder|DeliveryNote whereNumberPicks($value)
 * @method static Builder|DeliveryNote whereNumberStocks($value)
 * @method static Builder|DeliveryNote wherePackedAt($value)
 * @method static Builder|DeliveryNote wherePackerId($value)
 * @method static Builder|DeliveryNote wherePackingAt($value)
 * @method static Builder|DeliveryNote wherePickedAt($value)
 * @method static Builder|DeliveryNote wherePickerId($value)
 * @method static Builder|DeliveryNote wherePickingAt($value)
 * @method static Builder|DeliveryNote whereShipperId($value)
 * @method static Builder|DeliveryNote whereShopId($value)
 * @method static Builder|DeliveryNote whereSlug($value)
 * @method static Builder|DeliveryNote whereSourceId($value)
 * @method static Builder|DeliveryNote whereState($value)
 * @method static Builder|DeliveryNote whereSubmittedAt($value)
 * @method static Builder|DeliveryNote whereType($value)
 * @method static Builder|DeliveryNote whereUpdatedAt($value)
 * @method static Builder|DeliveryNote whereWeight($value)
 * @method static \Illuminate\Database\Query\Builder|DeliveryNote withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DeliveryNote withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery\DeliveryNoteItem> $deliveryNoteItems
 * @property-read int|null $delivery_note_items_count
 * @property-read \App\Models\Delivery\Shipment|null $shipments
 * @mixin \Eloquent
 */
class DeliveryNote extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data'               => 'array',
        'date'               => 'datetime',
        'order_submitted_at' => 'datetime',
        'assigned_at'        => 'datetime',
        'picking_at'         => 'datetime',
        'picked_at'          => 'datetime',
        'packing_at'         => 'datetime',
        'packed_at'          => 'datetime',
        'dispatched_at'      => 'datetime',
        'cancelled_at'       => 'datetime',

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->saveSlugsTo('slug');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'delivery_noteable');
    }


    public function stats(): HasOne
    {
        return $this->hasOne(DeliveryNoteStats::class);
    }

    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function shipments(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }


}
