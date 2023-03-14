<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\UI\Inventory\InventoryDashboard;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\Agent;

trait HasUIAgent
{
    public function getBreadcrumbs(Agent $agent): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                'procurement.agents.show' => [
                    'route'           => 'procurement.agents.show',
                    'routeParameters' => $agent->slug,
                    'name'            => $agent->code,
                    'index'           => [
                        'route'   => 'procurement.agents.index',
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
