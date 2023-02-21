<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:28:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Procurement\SupplierProductStats
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\SupplierProduct $supplierProduct
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats whereSupplierProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupplierProductStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupplierProductStats extends Model
{
    protected $table = 'supplier_product_stats';

    protected $guarded = [];


    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
