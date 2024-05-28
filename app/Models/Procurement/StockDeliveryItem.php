<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 14:32:31 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SupplyChain\SupplierProduct;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property int $stock_delivery_id
 * @property int $supplier_product_id
 * @property string $state
 * @property string|null $checked_at
 * @property array $data
 * @property string $unit_quantity
 * @property string $unit_quantity_checked
 * @property string $unit_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Procurement\StockDelivery $stockDelivery
 * @property-read SupplierProduct $supplierProduct
 * @method static \Database\Factories\Procurement\StockDeliveryItemFactory factory($count = null, $state = [])
 * @method static Builder|StockDeliveryItem newModelQuery()
 * @method static Builder|StockDeliveryItem newQuery()
 * @method static Builder|StockDeliveryItem query()
 * @mixin Eloquent
 */
class StockDeliveryItem extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function stockDelivery(): BelongsTo
    {
        return $this->belongsTo(StockDelivery::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
