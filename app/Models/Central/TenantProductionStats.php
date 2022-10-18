<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 09:15:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantProductionStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_products
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats whereNumberProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantProductionStats whereUpdatedAt($value)
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
