<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tenancy\TenantStats
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|TenantStats newModelQuery()
 * @method static Builder|TenantStats newQuery()
 * @method static Builder|TenantStats query()
 * @mixin Eloquent
 */
class TenantStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'tenant_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
