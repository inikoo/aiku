<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 16:37:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentOrganisation\AgentOrganisationStatusEnum;
use App\Models\SupplyChain\Agent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Procurement\AgentOrganisation
 *
 * @property int $id
 * @property int $agent_id
 * @property int $organisation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property AgentOrganisationStatusEnum $status
 * @property-read Agent $agent
 * @method static \Illuminate\Database\Eloquent\Builder|AgentOrganisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentOrganisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentOrganisation query()
 * @mixin \Eloquent
 */
class AgentOrganisation extends Pivot
{
    protected $table = 'agent_organisation';

    protected $casts = [
        'status' => AgentOrganisationStatusEnum::class
    ];


    protected $guarded = [];


    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

}
