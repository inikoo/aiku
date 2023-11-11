<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:28:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierProductStats
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Procurement\SupplierProduct $supplierProduct
 * @method static Builder|SupplierProductStats newModelQuery()
 * @method static Builder|SupplierProductStats newQuery()
 * @method static Builder|SupplierProductStats query()
 * @mixin Eloquent
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
