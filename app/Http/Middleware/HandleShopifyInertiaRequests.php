<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 11:59:42 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Actions\UI\Shopify\GetFirstLoadProps;
use App\Http\Resources\UI\LoggedShopifyUserResource;
use App\Models\CRM\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleShopifyInertiaRequests extends Middleware
{
    protected $rootView = 'app-shopify';


    public function share(Request $request): array
    {
        /** @var WebUser $webUser */
        $webUser = $request->user();

        $firstLoadOnlyProps = [];

        if (!$request->inertia() or Session::get('reloadLayout')) {

            $firstLoadOnlyProps          = GetFirstLoadProps::run($request, $webUser);
            $firstLoadOnlyProps['ziggy'] = function () use ($request) {
                return array_merge((new Ziggy())->toArray(), [
                    'location' => $request->url(),
                ]);
            };
        }

        return array_merge(
            $firstLoadOnlyProps,
            [
                'auth'  => [
                    'user' => $request->user() ? LoggedShopifyUserResource::make($request->user())->getArray() : null,
                ],
                'flash' => [
                    'notification' => fn () => $request->session()->get('notification')
                ],
                'ziggy' => [
                    'location' => $request->url(),
                ],
            ],
            parent::share($request),
        );

    }
}
