<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\Ordering\Transaction;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Dispatching\DeliveryNoteItem
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $delivery_note_id
 * @property int|null $stock_family_id
 * @property int|null $stock_id
 * @property int|null $org_stock_family_id
 * @property int|null $org_stock_id
 * @property int|null $transaction_id
 * @property int|null $invoice_transaction_id
 * @property string|null $notes
 * @property DeliveryNoteItemStateEnum $state
 * @property string|null $weight
 * @property string $quantity_required
 * @property string|null $quantity_picked
 * @property string|null $quantity_packed
 * @property string|null $quantity_dispatched
 * @property string $revenue_amount
 * @property string $org_revenue_amount
 * @property string $grp_revenue_amount
 * @property string|null $profit_amount
 * @property string|null $org_profit_amount
 * @property string|null $grp_profit_amount
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property-read \App\Models\Dispatching\DeliveryNote $deliveryNote
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read OrgStock|null $orgStock
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Dispatching\Packing|null $packings
 * @property-read \App\Models\Dispatching\Picking|null $pickings
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read Transaction|null $transaction
 * @method static Builder<static>|DeliveryNoteItem newModelQuery()
 * @method static Builder<static>|DeliveryNoteItem newQuery()
 * @method static Builder<static>|DeliveryNoteItem query()
 * @mixin Eloquent
 */
class DeliveryNoteItem extends Model
{
    use InShop;

    protected $table = 'delivery_note_items';

    protected $casts = [
        'data'   => 'array',
        'state'  => DeliveryNoteItemStateEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function pickings(): HasOne
    {
        return $this->hasOne(Picking::class);
    }

    public function packings(): HasOne
    {
        return $this->hasOne(Packing::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function orgStock(): BelongsTo
    {
        return $this->belongsTo(OrgStock::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
