<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 17:08:30 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser\Retina\UI;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class ShowRetinaPrepareAccount
{
    use AsController;


    public function handle(ActionRequest $request): Response
    {
        return Inertia::render('Errors/ErrorInApp', [
            'error' => [
                'code'        => 403,
                'title'       => 'We still prepare your account',
                'description' => 'please come back shortly.'
            ]
        ]);
    }
}
