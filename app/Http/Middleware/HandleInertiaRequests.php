<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 18:11:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Middleware;

use App\Actions\UI\GetCurrentShopSlug;
use App\Actions\UI\GetLayout;
use App\Actions\UI\GetShops;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\UI\LoggedUserResource;
use App\Models\Marketing\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';


    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\SysAdmin\User $user */
        $user = $request->user();

        $firstLoadOnlyProps = [];


        if (!$request->inertia() or Session::get('reloadLayout')) {
            $firstLoadOnlyProps = [

                'tenant'   => app('currentTenant') ? app('currentTenant')->only('name', 'code') : null,
                'language' => $user ? Arr::get($user->settings, 'language') : App::currentLocale(),


                'layoutCurrentShopSlug'   => function () use ($user) {
                    if ($user) {
                        return GetCurrentShopSlug::run($user);
                    } else {
                        return null;
                    }
                },


                'layoutShopsList'   => function () use ($user) {
                    if ($user) {
                        return GetShops::run($user);
                    } else {
                        return [];
                    }
                },

                'layout'   => function () use ($user) {
                    if ($user) {
                        return GetLayout::run($user);
                    } else {
                        return [];
                    }
                }
            ];

            if (Session::get('reloadLayout') == 'remove') {
                Session::forget('reloadLayout');
            }
            if (Session::get('reloadLayout')) {
                Session::put('reloadLayout', 'remove');
            }
        }



        return array_merge(
            parent::share($request),
            $firstLoadOnlyProps,
            [
                'auth'          => [
                    'user' => $request->user() ? new LoggedUserResource($request->user()) : null,
                ],
                'flash'         => [
                    'notification' => fn () => $request->session()->get('notification')
                ],

                'ziggy'         => function () use ($request) {
                    return array_merge((new Ziggy())->toArray(), [
                        'location' => $request->url(),
                    ]);
                },


                'searchQuery'       => fn () => $request->session()->get('fastSearchQuery'),
                'searchResults'     => function () use ($request) {
                    $query=$request->session()->get('fastSearchQuery');
                    if ($query) {
                        $items = Product::search($query)->paginate(5);
                        return ProductResource::collection($items);
                    } else {
                        return ['data' => []];
                    }
                },


            ]
        );
    }
}
