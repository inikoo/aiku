<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:32:21 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
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
        return $this->belongsTo(HistoricOuter::class);
    }
    */
}
