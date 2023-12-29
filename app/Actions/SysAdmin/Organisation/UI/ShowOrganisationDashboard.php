<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowOrganisationDashboard
{
    use AsAction;

    public function handle(): Response
    {
        return Inertia::render(
            'Dashboard/Dashboard',
            [
            'breadcrumbs' => $this->getBreadcrumbs(__('dashboard')),
            ]
        );
    }

    public function asController(Organisation $organisation): Response
    {
        return $this->handle();
    }

    public function getBreadcrumbs($label=null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-tachometer-alt-fast',
                    'label' => $label,
                    'route' => [
                        'name' => 'grp.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
