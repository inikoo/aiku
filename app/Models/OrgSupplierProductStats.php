<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 22:55:31 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Procurement\OrgSupplierProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrgSupplierProductStats
 *
 * @property int $id
 * @property int $org_supplier_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read OrgSupplierProduct $orgSupplierProduct
 * @property-read OrgSupplierProductStats|null $stats
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

    public function stats(): HasOne
    {
        return $this->hasOne(OrgSupplierProductStats::class);
    }
}
