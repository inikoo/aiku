<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 01:57:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandlePublicInertiaRequests extends Middleware
{
    protected $rootView = 'app-public';


    public function share(Request $request): array
    {
        $firstLoadOnlyProps['ziggy'] = function () use ($request) {
            return array_merge((new Ziggy())->toArray(), [
                'location' => $request->url(),
            ]);
        };


        return array_merge(
            $firstLoadOnlyProps,
            parent::share($request),
        );

    }
}
