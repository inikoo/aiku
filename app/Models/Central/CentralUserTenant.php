<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:26:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class CentralUserTenant extends Pivot
{
    use UsesLandlordConnection;

    public $incrementing = true;

    public $table = 'central_user_tenant';


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
