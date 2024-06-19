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
        $website   = request()->get('website');

        $home=$website->storefront;


        $structure = Arr::get(
            $website->structure,
            'header',
            [
                'header_name' => 'HeaderTypeA1',
                'header_data' => [
                    [
                        'name' => 'Image1',
                        'data' => [
                            'imgLogo' => 'https://www.aw-fulfilment.co.uk/wi.php?id=1837721'
                        ]
                    ],
                    [
                        'name' => 'Headline1',
                        'data' => [
                            'title'       => "Aiku: Fulfilment Warehouse",
                            'description' => "Processing and Fulfillment Operations"
                        ]
                    ],
                ]
            ]
        );



        return Inertia::render(
            'Home',
            [
                'header' => $structure,
                'blocks'=>$home->published_layout
            ]
        );
    }

}
