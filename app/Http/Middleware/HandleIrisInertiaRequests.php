<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 01:57:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleIrisInertiaRequests extends Middleware
{
    protected $rootView = 'app-iris';


    public function share(Request $request): array
    {
        $website                             = $request->get('website');
        $firstLoadOnlyProps['environment']   = app()->environment();
        $firstLoadOnlyProps['ziggy']         = function () use ($request) {
            return array_merge((new Ziggy())->toArray(), [
                'location' => $request->url()
            ]);
        };

        return array_merge(
            $firstLoadOnlyProps,
            [
                'iris' => [
                    'header'     => array_merge(
                        Arr::get($website->published_layout, 'header'),
                        [
                            'loginRoute' => [
                            'name' => 'retina.login.show'
                        ]
                    ]
                    ),
                    'footer'     => Arr::get($website->published_layout, 'footer'),
                    'menu'       => Arr::get($website->published_layout, 'menu'),
                    'color'      => Arr::get($website->published_layout, 'color')
                ],
                'user'     => $request->user()
            ],
            parent::share($request),
        );

    }
}
