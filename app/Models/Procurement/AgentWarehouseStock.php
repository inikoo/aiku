<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Goods an agent already holds in its own warehouse: received from the supplier, not yet in a container.
 *
 * @property int $id
 * @property int $group_id
 * @property int $agent_id
 * @property int $organisation_id
 * @property int $supplier_product_id
 * @property int|null $purchase_order_id
 * @property AgentWarehouseStockStateEnum $state
 * @property string $quantity
 * @property string|null $notes
 */
class AgentWarehouseStock extends Model
{
    use InGroup;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'state' => AgentWarehouseStockStateEnum::class,
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(AgentWarehouseReceipt::class);
    }
}
