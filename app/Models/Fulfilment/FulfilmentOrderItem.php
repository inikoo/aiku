<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 11:36:23 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SupplyChain\Stock;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Fulfilment\Fulfilment\FulfilmentOrderItem
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Fulfilment\FulfilmentOrder $FulfilmentOrder
 * @property-read Customer $customer
 * @property-read Shop $shop
 * @property-read Stock $stock
 * @method static Builder|FulfilmentOrderItem newModelQuery()
 * @method static Builder|FulfilmentOrderItem newQuery()
 * @method static Builder|FulfilmentOrderItem query()
 * @mixin Eloquent
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
    public function setQuantityAttribute($val): void
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }
}
