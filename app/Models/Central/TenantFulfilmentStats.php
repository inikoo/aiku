<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 04 Dec 2022 18:28:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\TenantFulfilmentStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_customers_with_stocks
 * @property int $number_customers_with_active_stocks
 * @property int $number_customers_with_assets
 * @property int $number_assets
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 *
 * @method static Builder|TenantFulfilmentStats newModelQuery()
 * @method static Builder|TenantFulfilmentStats newQuery()
 * @method static Builder|TenantFulfilmentStats query()
 *
 * @mixin \Eloquent
 */
class TenantFulfilmentStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_fulfilment_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
