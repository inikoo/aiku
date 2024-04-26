<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:51:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Iris;

use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsController;

class ShowHome
{
    use AsController;

    public function handle(): Response
    {
        /** @var Website $website */
        $website = request()->get('website');

        return Inertia::render(
            'Home',
            [
                'data' => [
                    'components' => [
                        [
                            'type'    => "header",
                            'content' => [
                                'imgLogo'     => Arr::get($website->structure['header'], 'imgLogo'),
                                'title'       => Arr::get($website->structure['header'], 'title'),
                                'description' => Arr::get($website->structure['header'], 'description')
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

}
