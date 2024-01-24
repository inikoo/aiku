<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 15:37:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetFulfilmentNavigation
{
    use AsAction;

    public function handle(Fulfilment $fulfilment, User $user): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("products.$fulfilment->id.view")) {
            $navigation['fulfilment'] = [
                'scope' => 'fulfilments',
                'icon'  => ['fal', 'fa-store-alt'],

                'label' => __('Fulfilment'),
                'route' => [
                    'name'       => 'grp.org.fulfilments.show',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],


                'topMenu' => [




                ],
            ];
        }


        return $navigation;
    }
}
