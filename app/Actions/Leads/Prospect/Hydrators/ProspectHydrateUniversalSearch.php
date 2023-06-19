<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Leads\Prospect\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Leads\Prospect;
use Lorisleiva\Actions\Concerns\AsAction;

class ProspectHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Prospect $prospect): void
    {
        $prospect->universalSearch()->create(
            [
                'section' => 'Leads',
                'route' => json_encode([
                    'name'      => 'procurement.agents.show',
                    'arguments' => [
                        $prospect->slug
                    ]
                ]),
                'icon' => 'fa-map-signs',
                'primary_term'   => $prospect->name.' '.$prospect->email,
                'secondary_term' => $prospect->contact_name.' '.$prospect->company_name
            ]
        );
    }

}
