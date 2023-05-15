<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentTenant\AgentTenantStatusEnum;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\AgentTenant
 *
 * @property int $id
 * @property int $agent_id
 * @property int $tenant_id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read \App\Models\Procurement\Agent $agent
 * @method static Builder|AgentTenant newModelQuery()
 * @method static Builder|AgentTenant newQuery()
 * @method static Builder|AgentTenant query()
 * @mixin \Eloquent
 */
class AgentTenant extends Pivot
{
    use UsesGroupConnection;

    protected $table = 'agent_tenant';

    protected $casts = [
        'status' => AgentTenantStatusEnum::class
    ];

    protected $guarded = [];


    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

}
