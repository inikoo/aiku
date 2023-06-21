<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\CRM\Prospect;
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
                'route'   => json_encode([
                    'name'      => 'procurement.agents.show',
                    'arguments' => [
                        $prospect->slug
                    ]
                ]),
                'icon'           => 'fa-map-signs',
                'primary_term'   => $prospect->name.' '.$prospect->email,
                'secondary_term' => $prospect->contact_name.' '.$prospect->company_name
            ]
        );
    }

}
