<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentOrganisation\AgentOrganisationStatusEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\AgentOrganisation
 *
 * @property AgentOrganisationStatusEnum $status
 * @property-read \App\Models\Procurement\Agent|null $agent
 * @method static Builder|AgentOrganisation newModelQuery()
 * @method static Builder|AgentOrganisation newQuery()
 * @method static Builder|AgentOrganisation query()
 * @mixin Eloquent
 */
class AgentOrganisation extends Pivot
{
    protected $table = 'agent_tenant';

    protected $casts = [
        'status' => AgentOrganisationStatusEnum::class
    ];

    protected $guarded = [];


    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

}
