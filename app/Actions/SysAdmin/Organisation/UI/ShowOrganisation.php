<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 03:25:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\InertiaAction;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrganisation extends InertiaAction
{
    private Organisation $organisation;


    public function handle(Organisation $organisation): Organisation
    {
        return $organisation;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('sysadmin.edit');
    }

    public function asController(Organisation $organisation): Organisation
    {
        $this->validateAttributes();
        return $this->handle($organisation);
    }


    public function htmlResponse(Organisation $organisation): Response
    {



        return Inertia::render(
            'Central/Account',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('account'),
                'pageHead'    => [
                    'title' => $organisation->name,
                ],

                'tabs' => [

                    'current' => 'account',
                    'items'   => [
                        'account' => [
                            'name' => __('Account'),
                            'icon' => 'fal fa-briefcase',
                        ],
                        'index'   => [
                            'name' => __('Index'),
                            'icon' => 'fal fa-indent',
                        ]
                    ]
                ],




            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [];
    }
}
