<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\JobPosition;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveJobPosition extends InertiaAction
{
    public function handle(JobPosition $jobPosition): JobPosition
    {
        return $jobPosition;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(JobPosition $jobPosition, ActionRequest $request): JobPosition
    {
        $this->initialisation($request);

        return $this->handle($jobPosition);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Job Position'),
            'text'        => __("This action will delete this Job Position"),
            'route'       => $route
        ];
    }

    public function htmlResponse(JobPosition $jobPosition, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete job position'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $jobPosition
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-inventory'],
                            'title' => __('job position')
                        ],
                    'title'  => $jobPosition->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name'       => 'models.job-position.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(JobPosition $jobPosition): array
    {
        return ShowJobPosition::make()->getBreadcrumbs($jobPosition, suffix: '('.__('deleting').')');
    }
}
