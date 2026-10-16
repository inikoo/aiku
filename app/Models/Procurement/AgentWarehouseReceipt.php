<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:31
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * One batch of a purchase order line the agent received from the supplier into its own warehouse, and the rejects
 * found when it was checked. What passed is held as the linked agent warehouse stock.
 *
 * @property int $id
 * @property int $group_id
 * @property int $agent_id
 * @property int $organisation_id
 * @property int $purchase_order_id
 * @property int $purchase_order_transaction_id
 * @property int $supplier_product_id
 * @property int|null $agent_warehouse_stock_id
 * @property Carbon $received_at
 * @property string $quantity_received
 * @property string|null $notes
 * @property string|null $quantity_rejected
 * @property Carbon|null $checked_at
 * @property string|null $reject_reason
 * @property int|null $received_by_id
 * @property int|null $checked_by_id
 * @property-read AgentWarehouseStock|null $agentWarehouseStock
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read PurchaseOrderTransaction $purchaseOrderTransaction
 */
class AgentWarehouseReceipt extends Model implements HasMedia
{
    use InGroup;
    use HasAttachments;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'checked_at'  => 'datetime',
        ];
    }

    public function isChecked(): bool
    {
        return $this->checked_at !== null;
    }

    public function quantityAccepted(): float
    {
        return (float) $this->quantity_received - (float) $this->quantity_rejected;
    }

    /**
     * Once its goods are packed for a container the receipt is history: it can no longer be changed.
     */
    public function isEditable(): bool
    {
        return $this->agentWarehouseStock?->state === AgentWarehouseStockStateEnum::RECEIVED;
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseOrderTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderTransaction::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function agentWarehouseStock(): BelongsTo
    {
        return $this->belongsTo(AgentWarehouseStock::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }
}
