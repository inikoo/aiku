<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 12:42:37 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\GuestTabsEnum;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\Auth\Guest;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGuest extends InertiaAction
{
    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $this->initialisation($request)->withTab(GuestTabsEnum::values());

        return $guest;
    }

    public function jsonResponse(Guest $guest): GuestResource
    {
        return new GuestResource($guest);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.users.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(Guest $guest, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/Guest',
            [
                'title'       => __('guest'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title'     => $guest->name,
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => GuestTabsEnum::navigation()
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Guest $guest, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('guests')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $guest->slug,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'sysadmin.guests.show',
            'sysadmin.guests.edit' =>

            array_merge(
                SysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['guest'],
                    [
                        'index' => [
                            'name'       => 'sysadmin.guests.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'sysadmin.guests.show',
                            'parameters' => [$routeParameters['guest']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),


            default => []
        };
    }


}
