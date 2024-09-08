<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 13:56:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SupplyChain\SupplierProduct;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\PurchaseOrderTransaction
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $purchase_order_id
 * @property int $supplier_product_id
 * @property int $historic_supplier_product_id
 * @property int $org_supplier_product_id
 * @property int $org_stock_id
 * @property string $state
 * @property string $status
 * @property string|null $quantity_ordered
 * @property string|null $quantity_dispatched
 * @property string|null $quantity_fail
 * @property string|null $quantity_cancelled
 * @property string $net_amount
 * @property string|null $grp_net_amount
 * @property string|null $org_net_amount
 * @property string|null $grp_exchange
 * @property string|null $org_exchange
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Procurement\PurchaseOrder $purchaseOrder
 * @property-read SupplierProduct $supplierProduct
 * @method static \Database\Factories\Procurement\PurchaseOrderTransactionFactory factory($count = null, $state = [])
 * @method static Builder|PurchaseOrderTransaction newModelQuery()
 * @method static Builder|PurchaseOrderTransaction newQuery()
 * @method static Builder|PurchaseOrderTransaction query()
 * @mixin Eloquent
 */
class PurchaseOrderTransaction extends Model
{
    use HasFactory;
    use InOrganisation;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
