<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:32:21 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Procurement\HistoricSupplierProductStats
 *
 * @method static Builder|HistoricSupplierProductStats newModelQuery()
 * @method static Builder|HistoricSupplierProductStats newQuery()
 * @method static Builder|HistoricSupplierProductStats query()
 * @mixin \Eloquent
 */
class HistoricSupplierProductStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'historic_supplier product_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricProduct::class);
    }
    */
}
