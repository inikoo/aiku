<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 18:16:08 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\TenantSalesStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static Builder|TenantSalesStats newModelQuery()
 * @method static Builder|TenantSalesStats newQuery()
 * @method static Builder|TenantSalesStats query()
 * @mixin \Eloquent
 */
class TenantSalesStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_sales_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
