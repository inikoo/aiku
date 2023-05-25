<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Tenancy\TenantFulfilmentStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_customers_with_stocks
 * @property int $number_customers_with_active_stocks
 * @property int $number_customers_with_assets
 * @property int $number_stored_items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|TenantFulfilmentStats newModelQuery()
 * @method static Builder|TenantFulfilmentStats newQuery()
 * @method static Builder|TenantFulfilmentStats query()
 * @mixin \Eloquent
 */
class TenantFulfilmentStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'tenant_fulfilment_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
