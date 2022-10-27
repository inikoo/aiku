<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 18:13:18 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantMarketingStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static Builder|TenantMarketingStats newModelQuery()
 * @method static Builder|TenantMarketingStats newQuery()
 * @method static Builder|TenantMarketingStats query()
 * @mixin \Eloquent
 */
class TenantMarketingStats extends Model
{
    protected $table = 'tenant_marketing_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
