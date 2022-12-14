<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 04 Dec 2022 18:28:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantFulfilmentStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|TenantFulfilmentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantFulfilmentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantFulfilmentStats query()
 * @mixin \Eloquent
 */
class TenantFulfilmentStats extends Model
{
    protected $table = 'tenant_fulfilment_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
