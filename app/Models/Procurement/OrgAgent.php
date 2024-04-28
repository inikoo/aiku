<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 16:37:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentOrganisation\AgentOrganisationStatusEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Procurement\OrgAgent
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property AgentOrganisationStatusEnum $status
 * @property-read Agent $agent
 * @property-read Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\OrgSupplier> $orgSuppliers
 * @property-read Organisation $organisation
 * @property-read \App\Models\Procurement\OrgAgentStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgAgent query()
 * @mixin \Eloquent
 */
class OrgAgent extends Model
{
    use InOrganisation;

    protected $casts = [
        'status' => AgentOrganisationStatusEnum::class
    ];


    protected $guarded = [];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrgAgentStats::class);
    }

    public function orgSuppliers(): HasMany
    {
        return $this->hasMany(OrgSupplier::class);
    }



}
