<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Models\SupplyChain\Agent;

trait HasUIAgent
{
    public function getBreadcrumbs(Agent $agent): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                'grp.procurement.agents.show' => [
                    'route'           => 'grp.procurement.agents.show',
                    'routeParameters' => $agent->slug,
                    'name'            => $agent->code,
                    'index'           => [
                        'route'   => 'grp.procurement.agents.index',
                        'overlay' => __('agents list')
                    ],
                    'modelLabel'      => [
                        'label' => __('agent')
                    ],
                ],
            ]
        );
    }
}
