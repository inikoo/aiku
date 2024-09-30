<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:40:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Procurement\HistoricSupplierProductStats
 *
 * @property int $id
 * @property int $historic_supplier_product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|HistoricSupplierProductStats newModelQuery()
 * @method static Builder|HistoricSupplierProductStats newQuery()
 * @method static Builder|HistoricSupplierProductStats query()
 * @mixin Eloquent
 */
class HistoricSupplierProductStats extends Model
{
    protected $table = 'historic_supplier_product_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricAsset::class);
    }
    */
}
