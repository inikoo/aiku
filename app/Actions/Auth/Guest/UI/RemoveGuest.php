<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest\UI;

use App\Actions\InertiaAction;
use App\Models\Auth\Guest;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveGuest extends InertiaAction
{
    public function handle(Guest $guest): Guest
    {
        return $guest;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('sysadmin.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $this->initialisation($request);

        return $this->handle($guest);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Guest'),
            'text'        => __("This action will delete this Guest"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Guest $guest, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete guest'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $guest,
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-user-alien'],
                            'title' => __('guest')
                        ],
                    'title'  => $guest->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => $guest->slug
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name'       => 'models.guest.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(Guest $guest, array $routeParameters): array
    {
        return ShowGuest::make()->getBreadcrumbs($guest, $routeParameters, suffix: '('.__('deleting').')');
    }
}
