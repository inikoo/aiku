<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 09:15:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantProductionStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static Builder|TenantProductionStats newModelQuery()
 * @method static Builder|TenantProductionStats newQuery()
 * @method static Builder|TenantProductionStats query()
 * @mixin \Eloquent
 */
class TenantProductionStats extends Model
{
    protected $table = 'tenant_production_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
