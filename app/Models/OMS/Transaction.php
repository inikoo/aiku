<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:35:59 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\OMS;

use App\Enums\OMS\Transaction\TransactionStateEnum;
use App\Enums\OMS\Transaction\TransactionStatusEnum;
use App\Enums\OMS\Transaction\TransactionTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Market\Shop;
use App\Models\Traits\InCustomer;
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

/**
 * App\Models\Ordering\Transaction
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $order_id
 * @property int|null $invoice_id
 * @property TransactionTypeEnum $type
 * @property string $date
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
 * @property string $group_net_amount
 * @property string $org_net_amount
 * @property string $tax_rate
 * @property string $group_exchange
 * @property string $org_exchange
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Customer $customer
 * @property-read Collection<int, DeliveryNoteItem> $deliveryNoteItems
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Model|\Eloquent $item
 * @property-read \App\Models\OMS\Order|null $order
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-write mixed $quantity
 * @property-read Shop $shop
 * @method static \Database\Factories\OMS\TransactionFactory factory($count = null, $state = [])
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
    use SoftDeletes;
    use HasFactory;
    use InCustomer;

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



    /** @noinspection PhpUnused */
    public function setQuantityAttribute($val): void
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }
}
