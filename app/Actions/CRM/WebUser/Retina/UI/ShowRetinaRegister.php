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
use App\Actions\Helpers\Country\UI\GetAddressData;

class ShowRetinaRegister
{
    use AsController;


    public function handle(ActionRequest $request): Response
    {
        $fulfilment = $request->website->shop->fulfilment;
        return Inertia::render(
            'Auth/Register',
            [
            'countriesAddressData' => GetAddressData::run(),
            'registerRoute' => [
                'name' => 'retina.register.store',
                'parameters' => [
                    'fulfilment' => $fulfilment->id
                ]
            ]
        ]
        );
    }

}
