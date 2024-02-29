<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 14:12:54 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Storage;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowStorageDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {

        return Inertia::render('Storage/StorageDashboard');
    }

    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-home',
                    'label' => $label,
                    'route' => [
                        'name' => 'retina.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
