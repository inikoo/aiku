<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:26:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Tenancy\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\CentralUserTenant
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $central_user_id
 * @property bool $status
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|CentralUserTenant newModelQuery()
 * @method static Builder|CentralUserTenant newQuery()
 * @method static Builder|CentralUserTenant query()
 * @mixin \Eloquent
 */
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
