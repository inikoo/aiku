<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SupplierTenant extends Pivot
{
    use UsesGroupConnection;
    protected $table = 'supplier_tenant';
}
