<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:40:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Delivery;

use App\Models\CRM\Customer;
use App\Models\Marketing\Shop;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Delivery\DeliveryNote
 *
 * @method static Builder|DeliveryNote newModelQuery()
 * @method static Builder|DeliveryNote newQuery()
 * @method static Builder|DeliveryNote query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property int $order_id Main order, usually the only one (used for performance)
 * @property string $number
 * @property string $type
 * @property string $state
 * @property int|null $delivery_address_id
 * @property int|null $shipper_id
 * @property string|null $weight
 * @property int $number_stocks
 * @property int $number_picks
 * @property int|null $picker_id Main picker
 * @property int|null $packer_id Main packer
 * @property string $date
 * @property string|null $order_submitted_at
 * @property string|null $assigned_at
 * @property string|null $picking_at
 * @property string|null $picked_at
 * @property string|null $packing_at
 * @property string|null $packed_at
 * @property string|null $dispatched_at
 * @property string|null $cancelled_at
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $organisation_source_id
 * @method static Builder|DeliveryNote whereAssignedAt($value)
 * @method static Builder|DeliveryNote whereCancelledAt($value)
 * @method static Builder|DeliveryNote whereCreatedAt($value)
 * @method static Builder|DeliveryNote whereCustomerId($value)
 * @method static Builder|DeliveryNote whereData($value)
 * @method static Builder|DeliveryNote whereDate($value)
 * @method static Builder|DeliveryNote whereDeletedAt($value)
 * @method static Builder|DeliveryNote whereDeliveryAddressId($value)
 * @method static Builder|DeliveryNote whereDispatchedAt($value)
 * @method static Builder|DeliveryNote whereId($value)
 * @method static Builder|DeliveryNote whereNumber($value)
 * @method static Builder|DeliveryNote whereNumberPicks($value)
 * @method static Builder|DeliveryNote whereNumberStocks($value)
 * @method static Builder|DeliveryNote whereOrderId($value)
 * @method static Builder|DeliveryNote whereOrderSubmittedAt($value)
 * @method static Builder|DeliveryNote whereOrganisationSourceId($value)
 * @method static Builder|DeliveryNote wherePackedAt($value)
 * @method static Builder|DeliveryNote wherePackerId($value)
 * @method static Builder|DeliveryNote wherePackingAt($value)
 * @method static Builder|DeliveryNote wherePickedAt($value)
 * @method static Builder|DeliveryNote wherePickerId($value)
 * @method static Builder|DeliveryNote wherePickingAt($value)
 * @method static Builder|DeliveryNote whereShipperId($value)
 * @method static Builder|DeliveryNote whereShopId($value)
 * @method static Builder|DeliveryNote whereState($value)
 * @method static Builder|DeliveryNote whereType($value)
 * @method static Builder|DeliveryNote whereUpdatedAt($value)
 * @method static Builder|DeliveryNote whereWeight($value)
 */
class DeliveryNote extends Model
{
    use SoftDeletes;

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


}
