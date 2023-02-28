<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:57:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantAccountingStats
 *
 * @property-read \App\Models\Central\Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|TenantAccountingStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantAccountingStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantAccountingStats query()
 * @mixin \Eloquent
 */
class TenantAccountingStats extends Model
{
    protected $table = 'tenant_accounting_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
