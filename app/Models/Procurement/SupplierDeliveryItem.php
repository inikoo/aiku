<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 14:32:31 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Procurement\SupplierDeliveryItem
 *
 * @property int $id
 * @property int $supplier_delivery_id
 * @property int $supplier_product_id
 * @property array $data
 * @property string $unit_quantity
 * @property string $unit_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Procurement\SupplierDelivery $supplierDelivery
 * @property-read \App\Models\Procurement\SupplierProduct $supplierProduct
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierDeliveryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierDeliveryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierDeliveryItem query()
 * @mixin \Eloquent
 */
class SupplierDeliveryItem extends Model
{
    use UsesLandlordConnection;

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
