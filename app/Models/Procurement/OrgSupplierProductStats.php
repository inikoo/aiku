<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:11:49 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrgSupplierProductStats
 *
 * @property int $id
 * @property int $org_supplier_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\OrgSupplierProduct $orgSupplierProduct
 * @method static \Illuminate\Database\Eloquent\Builder|OrgSupplierProductStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgSupplierProductStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgSupplierProductStats query()
 * @mixin \Eloquent
 */
class OrgSupplierProductStats extends Model
{
    protected $table = 'org_supplier_product_stats';

    protected $guarded = [];

    public function orgSupplierProduct(): BelongsTo
    {
        return $this->belongsTo(OrgSupplierProduct::class);
    }

}
