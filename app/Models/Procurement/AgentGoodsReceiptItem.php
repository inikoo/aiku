<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\Procurement;

use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Units of one purchase order line a goods receipt brought.
 *
 * @property int $id
 * @property int $agent_goods_receipt_id
 * @property int $organisation_id
 * @property int|null $purchase_order_id
 * @property int|null $purchase_order_transaction_id
 * @property int $supplier_product_id
 * @property string $quantity
 */
class AgentGoodsReceiptItem extends Model
{
    protected $guarded = [];

    public function agentGoodsReceipt(): BelongsTo
    {
        return $this->belongsTo(AgentGoodsReceipt::class);
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
}
