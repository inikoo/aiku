<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:40:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\HistoricSupplierProductStats
 *
 * @property int $id
 * @property int $historic_supplier_product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
