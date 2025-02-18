<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Layout;

use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPupilLayout
{
    use AsAction;

    public function handle($request, ?WebUser $webUser): array
    {
        $website    = $request->get('website');

        return [
            'website'  => GroupResource::make($request->get('website'))->getArray(),
            'customer' => CustomersResource::make($webUser->customer)->getArray(),
            'navigation' => match ($request->get('website')->type->value) {
                'fulfilment' => GetRetinaFulfilmentNavigation::run($webUser),
                'dropshipping' => GetRetinaDropshippingNavigation::run($webUser, $request),
                'b2b' => GetRetinaB2bNavigation::run($webUser),
                default      => []
            },
        ];
    }
}
