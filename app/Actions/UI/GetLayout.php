<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\UI;


use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $navigation = [
            [
                'name'  => __('dashboard'),
                'icon'  => ['fal', 'fa-home'],
                'route' => 'dashboard'
            ]
        ];


        if ($user->can('warehouses.dispatching.pick')) {
            $navigation[] = [
                'name'  => __('picking'),
                'icon'  => ['fal', 'fa-dolly-flatbed-alt'],
                'route' => 'dashboard'
            ];
        }

        if ($user->can('warehouses.dispatching.pack')) {
            $navigation[] = [
                'name'  => __('packing'),
                'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                'route' => 'dashboard'
            ];
        }

        if ($user->can('employees.view')) {
            $navigation[] = [
                'name'  => 'Employees',
                'icon'  => ['fal', 'fa-user-hard-hat'],
                'route' => 'hr.employees.index'
            ];
        }

        if ($user->can('users.view')) {
            $navigation[] = [
                'name'  => __('users'),
                'icon'  => ['fal', 'fa-users'],
                'route' => 'sysadmin.users.index'
            ];
        }


        return [
            'navigation' => $navigation
        ];
    }

}
