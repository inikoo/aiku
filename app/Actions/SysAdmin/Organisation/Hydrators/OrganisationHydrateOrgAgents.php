<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 11:12:58 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateOrgAgents
{
    use AsAction;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {
        $numberAgents = $organisation->orgAgents()->count();
        $activeAgents = $organisation->orgAgents()->where('org_agents.status', true)->count();

        $stats = [
            'number_org_agents'          => $numberAgents,
            'number_active_org_agents'   => $activeAgents,
            'number_archived_org_agents' => $numberAgents - $activeAgents
        ];


        $organisation->procurementStats()->update($stats);
    }
}
