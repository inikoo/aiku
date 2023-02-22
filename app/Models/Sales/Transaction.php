<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:15:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Sales;

use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\Sales\Transaction
 *
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property int $order_id
 * @property string|null $state
 * @property string|null $item_type
 * @property int|null $item_id
 * @property string $quantity
 * @property string $discounts
 * @property string $net
 * @property int|null $tax_band_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\DeliveryNoteItem> $deliveryNoteItems
 * @property-read int|null $delivery_note_items_count
 * @property-read Model|\Eloquent $item
 * @property-read \App\Models\Sales\Order $order
 * @property-read Shop $shop
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction onlyTrashed()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereCustomerId($value)
 * @method static Builder|Transaction whereData($value)
 * @method static Builder|Transaction whereDeletedAt($value)
 * @method static Builder|Transaction whereDiscounts($value)
 * @method static Builder|Transaction whereId($value)
 * @method static Builder|Transaction whereItemId($value)
 * @method static Builder|Transaction whereItemType($value)
 * @method static Builder|Transaction whereNet($value)
 * @method static Builder|Transaction whereOrderId($value)
 * @method static Builder|Transaction whereQuantity($value)
 * @method static Builder|Transaction whereShopId($value)
 * @method static Builder|Transaction whereSourceId($value)
 * @method static Builder|Transaction whereState($value)
 * @method static Builder|Transaction whereTaxBandId($value)
 * @method static Builder|Transaction whereUpdatedAt($value)
 * @method static Builder|Transaction withTrashed()
 * @method static Builder|Transaction withoutTrashed()
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'transactions';

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    /** @noinspection PhpUnused */
    public function setQuantityAttribute($val)
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }


}
