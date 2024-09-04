<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:35:59 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\HistoricAsset;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Catalogue\Shop;
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
 * @property string $date
 * @property string|null $submitted_at
 * @property string|null $in_warehouse_at
 * @property string|null $settled_at
 * @property TransactionStateEnum $state
 * @property TransactionStatusEnum $status
 * @property string|null $asset_type
 * @property int|null $asset_id
 * @property int|null $historic_asset_id
 * @property string|null $quantity_ordered
 * @property string|null $quantity_bonus
 * @property string|null $quantity_dispatched
 * @property string|null $quantity_fail
 * @property string|null $quantity_cancelled
 * @property string|null $fail_status
 * @property string $gross_amount net amount before discounts
 * @property string $net_amount
 * @property string|null $grp_net_amount
 * @property string|null $org_net_amount
 * @property int $tax_category_id
 * @property string|null $grp_exchange
 * @property string|null $org_exchange
 * @property array $data
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $alt_source_id to be used in no products transactions
 * @property-read Asset|null $asset
 * @property-read Customer $customer
 * @property-read Collection<int, DeliveryNoteItem> $deliveryNoteItems
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read HistoricAsset|null $historicAsset
 * @property-read Model|\Eloquent $item
 * @property-read \App\Models\Ordering\Order|null $order
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read Shop $shop
 * @method static \Database\Factories\Ordering\TransactionFactory factory($count = null, $state = [])
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
        'quantity'   => 'decimal:3',
        'data'       => 'array',
        'state'      => TransactionStateEnum::class,
        'status'     => TransactionStatusEnum::class,

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

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function historicAsset(): BelongsTo
    {
        return $this->belongsTo(HistoricAsset::class);
    }




}
