<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Procurement\Agent;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Agent $agent): void
    {
        $agent->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'procurement',
                'title'          => trim($agent->name.' '.$agent->email.' '.$agent->company_name.' '.$agent->contact_name),
                'description'    => ''
            ]
        );
    }

}
