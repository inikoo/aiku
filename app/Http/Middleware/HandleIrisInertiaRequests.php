<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 01:57:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Http\Resources\UI\LoggedWebUserResource;
use App\Models\CRM\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleIrisInertiaRequests extends Middleware
{
    protected $rootView = 'app-iris';


    public function share(Request $request): array
    {
        /** @var WebUser $webUser */
        $webUser = Auth::guard('retina')->user();

        $website                           = $request->get('website');
        $firstLoadOnlyProps['environment'] = app()->environment();
        $firstLoadOnlyProps['ziggy']       = function () use ($request) {
            return array_merge((new Ziggy())->toArray(), [
                'location' => $request->url()
            ]);
        };

        return array_merge(
            $firstLoadOnlyProps,
            [
                // 'auth'  => $webUser ? LoggedWebUserResource::make($webUser)->getArray() : null,
                'auth'  => [
                    'name' => 'John Doe'
                ],
                'flash' => [
                    'notification' => fn () => $request->session()->get('notification')
                ],
                'ziggy' => [
                    'location' => $request->url(),
                ],

                'iris' => [
                    'header' => array_merge(
                        Arr::get($website->published_layout, 'header'),
                        [
                            'loginRoute' => [
                                'name' => 'retina.login.show'
                            ]
                        ]
                    ),
                    'footer' => Arr::get($website->published_layout, 'footer'),
                    'menu'   => Arr::get($website->published_layout, 'menu'),
                    'theme'  => Arr::get($website->published_layout, 'theme'),
                    'variables' => [
                        'name'              => 'Vika Aqordi',
                        'username'          => 'aqordeon',
                        'email'             => 'aqordeon@mail.com',
                        'favourites_count'  => 45,
                        'cart_count'        => 12,
                        'cart_amount'       => '$99.124',
                    ]
                ],

            ],
            parent::share($request),
        );
    }
}
