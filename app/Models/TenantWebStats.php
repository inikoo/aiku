<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 00:40:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Tenancy\Tenant;
use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\TenantWebStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_websites
 * @property int $number_websites_under_maintenance
 * @property int $number_websites_type_info
 * @property int $number_websites_type_b2b
 * @property int $number_websites_type_b2c
 * @property int $number_websites_type_dropshipping
 * @property int $number_websites_type_fulfilment
 * @property int $number_websites_state_in_process
 * @property int $number_websites_state_live
 * @property int $number_websites_state_closed
 * @property int $number_websites_engine_aurora
 * @property int $number_websites_engine_iris
 * @property int $number_websites_engine_other
 * @property int $number_webpages
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @method static Builder|TenantWebStats newModelQuery()
 * @method static Builder|TenantWebStats newQuery()
 * @method static Builder|TenantWebStats query()
 * @mixin Eloquent
 */
class TenantWebStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'tenant_web_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
