<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dispatch\DeliveryNote
 *
 * @property int $id
 * @property string $slug
 * @property int $shop_id
 * @property int $customer_id
 * @property string $number
 * @property string $type
 * @property string $state
 * @property bool|null $can_dispatch
 * @property bool|null $restocking
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $shipment_id
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
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\DeliveryNoteItem> $deliveryNoteItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \App\Models\Dispatch\Shipment|null $shipments
 * @property-read Shop $shop
 * @property-read \App\Models\Dispatch\DeliveryNoteStats|null $stats
 * @method static Builder|DeliveryNote newModelQuery()
 * @method static Builder|DeliveryNote newQuery()
 * @method static Builder|DeliveryNote onlyTrashed()
 * @method static Builder|DeliveryNote query()
 * @method static Builder|DeliveryNote withTrashed()
 * @method static Builder|DeliveryNote withoutTrashed()
 * @mixin \Eloquent
 */
class DeliveryNote extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasAddress;
    use UsesTenantConnection;

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
