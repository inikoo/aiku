<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 10:41:08 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;


use App\Models\Traits\HasOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

/**
 * App\Models\Fulfilment\FulfilmentOrder
 *
 * @property int $id
 * @property string $slug
 * @property string|null $number
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $customer_client_id
 * @property string $state
 * @property bool|null $is_picking_on_hold
 * @property bool|null $can_dispatch
 * @property array $data
 * @property string|null $sent_warehouse_at
 * @property string|null $ready_to_dispatch_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery\DeliveryNote> $deliveryNotes
 * @property-read int|null $delivery_notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\FulfilmentOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Marketing\Shop $shop
 * @property-read \App\Models\Fulfilment\FulfilmentOrderStats|null $stats
 * @method static Builder|FulfilmentOrder newModelQuery()
 * @method static Builder|FulfilmentOrder newQuery()
 * @method static Builder|FulfilmentOrder onlyTrashed()
 * @method static Builder|FulfilmentOrder query()
 * @method static Builder|FulfilmentOrder whereCanDispatch($value)
 * @method static Builder|FulfilmentOrder whereCreatedAt($value)
 * @method static Builder|FulfilmentOrder whereCustomerClientId($value)
 * @method static Builder|FulfilmentOrder whereCustomerId($value)
 * @method static Builder|FulfilmentOrder whereData($value)
 * @method static Builder|FulfilmentOrder whereDeletedAt($value)
 * @method static Builder|FulfilmentOrder whereId($value)
 * @method static Builder|FulfilmentOrder whereIsPickingOnHold($value)
 * @method static Builder|FulfilmentOrder whereNumber($value)
 * @method static Builder|FulfilmentOrder whereReadyToDispatchAt($value)
 * @method static Builder|FulfilmentOrder whereSentWarehouseAt($value)
 * @method static Builder|FulfilmentOrder whereShopId($value)
 * @method static Builder|FulfilmentOrder whereSlug($value)
 * @method static Builder|FulfilmentOrder whereState($value)
 * @method static Builder|FulfilmentOrder whereUpdatedAt($value)
 * @method static Builder|FulfilmentOrder withTrashed()
 * @method static Builder|FulfilmentOrder withoutTrashed()
 * @mixin \Eloquent
 */
class FulfilmentOrder extends Model
{
    use HasOrder;
    use HasSlug;
    use SoftDeletes;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(FulfilmentOrderItem::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(FulfilmentOrderStats::class);
    }


}
