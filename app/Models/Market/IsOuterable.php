<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Apr 2024 19:31:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait IsOuterable
{
    use InShop;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
