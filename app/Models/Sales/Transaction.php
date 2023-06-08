<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:15:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Sales;

use App\Enums\Sales\Transaction\TransactionStateEnum;
use App\Enums\Sales\Transaction\TransactionStatusEnum;
use App\Enums\Sales\Transaction\TransactionTypeEnum;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Marketing\Shop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Sales\Transaction
 *
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $order_id
 * @property int|null $invoice_id
 * @property TransactionTypeEnum $type
 * @property TransactionStateEnum $state
 * @property TransactionStatusEnum $status
 * @property string|null $item_type
 * @property int|null $item_id
 * @property string $quantity_ordered
 * @property string $quantity_bonus
 * @property string $quantity_dispatched
 * @property string $quantity_fail
 * @property string $quantity_cancelled
 * @property string $discounts
 * @property string $net
 * @property int|null $tax_band_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Sales\Customer $customer
 * @property-read Collection<int, DeliveryNoteItem> $deliveryNoteItems
 * @property-read Model|\Eloquent $item
 * @property-read \App\Models\Sales\Order|null $order
 * @property-write mixed $quantity
 * @property-read Shop $shop
 * @method static \Database\Factories\Sales\TransactionFactory factory($count = null, $state = [])
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction onlyTrashed()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction withTrashed()
 * @method static Builder|Transaction withoutTrashed()
 * @mixin Eloquent
 */
class Transaction extends Model
{
    use UsesTenantConnection;
    use SoftDeletes;
    use HasFactory;

    protected $table = 'transactions';

    protected $casts = [
        'data'   => 'array',
        'state'  => TransactionStateEnum::class,
        'status' => TransactionStatusEnum::class,
        'type'   => TransactionTypeEnum::class,

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
    public function setQuantityAttribute($val): void
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }
}
