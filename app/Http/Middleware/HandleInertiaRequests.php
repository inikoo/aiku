<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 18:11:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Middleware;

use App\Actions\UI\GetLayout;
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


        $firstLoadOnlyProps = (!$request->inertia() or Session::get('redirectFromLogin')) ? [

            'tenant'   => tenant()->only('name', 'code'),
            'language' => $user ? Arr::get($user->settings, 'language') : App::currentLocale(),
            'layout'   => function () use ($user) {
                if ($user) {
                    return GetLayout::run($user);
                } else {
                    return [];
                }
            }
        ] : [];

        Session::forget('redirectFromLogin');


        return array_merge(
            parent::share($request),
            $firstLoadOnlyProps,
            [
                'auth'  => [
                    'user' => $request->user(),
                ],
                'ziggy' => function () use ($request) {
                    return array_merge((new Ziggy)->toArray(), [
                        'location' => $request->url(),
                    ]);
                },

            ]
        );
    }
}
