<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Feb 2024 10:36:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Http\Resources\Web\WebsiteResource;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleRetinaInertiaRequests extends Middleware
{
    protected $rootView = 'app-retina';


    public function share(Request $request): array
    {
        $firstLoadOnlyProps['ziggy'] = function () use ($request) {
            return array_merge((new Ziggy())->toArray(), [
                'location' => $request->url(),
            ]);
        };
        data_set($firstLoadOnlyProps, 'website', WebsiteResource::make($request->get('website'))->getArray());


        return array_merge(
            $firstLoadOnlyProps,
            parent::share($request),
        );

    }
}
