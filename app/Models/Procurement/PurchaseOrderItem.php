<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 13:56:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Database\Factories\Procurement\PurchaseOrderItemFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\PurchaseOrderItem
 *
 * @property int $id
 * @property int $purchase_order_id
 * @property int $supplier_product_id
 * @property string $state
 * @property string $status
 * @property array $data
 * @property string $unit_quantity
 * @property string $unit_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read SupplierProduct $supplierProduct
 * @method static PurchaseOrderItemFactory factory($count = null, $state = [])
 * @method static Builder|PurchaseOrderItem newModelQuery()
 * @method static Builder|PurchaseOrderItem newQuery()
 * @method static Builder|PurchaseOrderItem query()
 * @mixin Eloquent
 */
class PurchaseOrderItem extends Model
{
    use UsesGroupConnection;
    use HasFactory;

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
