<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 20:00:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;

class AttachAgentToOrganisation extends OrgAction
{
    public function handle(Organisation $organisation, Agent $agent, array $modelData = []): Organisation
    {
        $organisation->agents()->attach($agent, $modelData);
        foreach ($agent->suppliers as $supplier) {
            $organisation->suppliers()->attach($supplier, $modelData);
        }

        return $organisation;
    }

}
