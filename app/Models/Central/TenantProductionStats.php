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
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\TenantProductionStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_products
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static Builder|TenantProductionStats newModelQuery()
 * @method static Builder|TenantProductionStats newQuery()
 * @method static Builder|TenantProductionStats query()
 * @mixin \Eloquent
 */
class TenantProductionStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_production_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
