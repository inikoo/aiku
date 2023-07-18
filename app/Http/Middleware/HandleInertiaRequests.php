<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 18:11:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Middleware;

use App\Actions\UI\GetFirstLoadProps;
use App\Http\Resources\UI\LoggedUserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $firstLoadOnlyProps = [];


        if (!$request->inertia() or Session::get('reloadLayout')) {
            $firstLoadOnlyProps          =GetFirstLoadProps::run($user);
            $firstLoadOnlyProps['ziggy'] = function () use ($request) {
                return array_merge((new Ziggy())->toArray(), [
                    'location' => $request->url(),
                ]);
            };
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
                    'user' => $request->user() ? LoggedUserResource::make($request->user())->getArray() : null,
                ],
                'flash'         => [
                    'notification' => fn () => $request->session()->get('notification')
                ],
                'ziggy' => [
                    'location' => $request->url(),
                ],

            ]
        );
    }
}
