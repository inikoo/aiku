<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:25:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;


/**
 * App\Models\Central\TenantStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_employees
 * @property int $number_employees_state_hired
 * @property int $number_employees_state_working
 * @property int $number_employees_state_left
 * @property int $number_guests
 * @property int $number_guests_status_active
 * @property int $number_guests_status_inactive
 * @property int $number_users
 * @property int $number_users_status_active
 * @property int $number_users_status_inactive
 * @property int $number_users_type_tenant
 * @property int $number_users_type_employee
 * @property int $number_users_type_guest
 * @property int $number_users_type_supplier
 * @property int $number_users_type_agent
 * @property int $number_users_type_customer
 * @property int $number_images
 * @property int $filesize_images
 * @property int $number_attachments
 * @property int $filesize_attachments
 * @property bool $has_fulfilment
 * @property bool $has_dropshipping
 * @property bool $has_production
 * @property bool $has_agents
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static Builder|TenantStats newModelQuery()
 * @method static Builder|TenantStats newQuery()
 * @method static Builder|TenantStats query()
 * @mixin \Eloquent
 */
class TenantStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
