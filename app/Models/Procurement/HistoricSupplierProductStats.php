<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:32:21 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class HistoricSupplierProductStats extends Model
{
    protected $table = 'historic_supplier product_stats';

    protected $guarded = [];

    /*
    public function historicProduct(): BelongsTo
    {
        return $this->belongsTo(HistoricProduct::class);
    }
    */
}
