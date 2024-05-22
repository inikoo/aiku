<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Layout;

use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle($request, ?WebUser $webUser): array
    {
        if (!$webUser) {
            return [];
        }


        return [
            'website'         => GroupResource::make($request->get('website'))->getArray(),

            'navigation'      => match ($request->get('website')->type->value) {
                'fulfilment' => GetRetinaFulfilmentNavigation::run($webUser),
                default      => []
            },
        ];
    }
}
