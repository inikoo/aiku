<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveWorkingPlace extends InertiaAction
{
    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($request);

        return $this->handle($workplace);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Working Place'),
            'text'        => __("This action will delete this working place and all it's clocking machines & clockings"),
            'route'       => $route
        ];
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function htmlResponse(Workplace $workplace, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete working place'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $workplace
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-building'],
                            'title' => __('working place')
                        ],
                    'title'  => $workplace->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => $workplace
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name'       => 'models.working-place.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(Workplace $workplace): array
    {
        return ShowWorkingPlace::make()->getBreadcrumbs($workplace, suffix: '('.__('deleting').')');
    }
}
