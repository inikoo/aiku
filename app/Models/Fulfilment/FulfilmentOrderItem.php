<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 11:36:23 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\Inventory\Stock;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fulfilment\FulfilmentOrderItem
 *
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property int $fulfilment_order_id
 * @property string $state
 * @property string|null $item_type
 * @property int|null $item_id
 * @property string $quantity
 * @property string|null $notes
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Fulfilment\FulfilmentOrder $FulfilmentOrder
 * @property-read Customer $customer
 * @property-read Shop $shop
 * @property-read Stock $stock
 * @method static Builder|FulfilmentOrderItem newModelQuery()
 * @method static Builder|FulfilmentOrderItem newQuery()
 * @method static Builder|FulfilmentOrderItem query()
 * @method static Builder|FulfilmentOrderItem whereCreatedAt($value)
 * @method static Builder|FulfilmentOrderItem whereCustomerId($value)
 * @method static Builder|FulfilmentOrderItem whereData($value)
 * @method static Builder|FulfilmentOrderItem whereDeletedAt($value)
 * @method static Builder|FulfilmentOrderItem whereFulfilmentOrderId($value)
 * @method static Builder|FulfilmentOrderItem whereId($value)
 * @method static Builder|FulfilmentOrderItem whereItemId($value)
 * @method static Builder|FulfilmentOrderItem whereItemType($value)
 * @method static Builder|FulfilmentOrderItem whereNotes($value)
 * @method static Builder|FulfilmentOrderItem whereQuantity($value)
 * @method static Builder|FulfilmentOrderItem whereShopId($value)
 * @method static Builder|FulfilmentOrderItem whereState($value)
 * @method static Builder|FulfilmentOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FulfilmentOrderItem extends Model
{
    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function FulfilmentOrder(): BelongsTo
    {
        return $this->belongsTo(FulfilmentOrder::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
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
