<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 12:29:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantProcurementStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static Builder|TenantProcurementStats newModelQuery()
 * @method static Builder|TenantProcurementStats newQuery()
 * @method static Builder|TenantProcurementStats query()
 * @mixin \Eloquent
 */
class TenantProcurementStats extends Model
{
    protected $table = 'tenant_procurement_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
