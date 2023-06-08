<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 14:32:31 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierDeliveryItem
 *
 * @property int $id
 * @property int $supplier_delivery_id
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
 * @property-read \App\Models\Procurement\SupplierDelivery $supplierDelivery
 * @property-read \App\Models\Procurement\SupplierProduct $supplierProduct
 * @method static \Database\Factories\Procurement\SupplierDeliveryItemFactory factory($count = null, $state = [])
 * @method static Builder|SupplierDeliveryItem newModelQuery()
 * @method static Builder|SupplierDeliveryItem newQuery()
 * @method static Builder|SupplierDeliveryItem query()
 * @mixin Eloquent
 */
class SupplierDeliveryItem extends Model
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

    public function supplierDelivery(): BelongsTo
    {
        return $this->belongsTo(SupplierDelivery::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
