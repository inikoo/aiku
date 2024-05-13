<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 13:04:20 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Devel\UI;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

trait IsDummy
{
    use AsAction;

    public function asController(ActionRequest $request): ActionRequest
    {
        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Devel/Dummy',
            [

                'title'    => __('dummy'),
                'pageHead' => [
                    'title' => $request->route()->getName()
                ],


            ]
        );
    }
}
