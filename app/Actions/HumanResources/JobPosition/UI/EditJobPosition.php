<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\OrgAction;
use App\Models\HumanResources\JobPosition;
use App\Models\Catalogue\ProductCategory;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditJobPosition extends OrgAction
{
    public function handle(JobPosition $jobPosition): JobPosition
    {
        return $jobPosition;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, JobPosition $jobPosition, ActionRequest $request): JobPosition
    {
        $this->initialisation($organisation, $request);

        return $this->handle($jobPosition);
    }


    /**
     * @throws Exception
     */
    public function htmlResponse(JobPosition $jobPosition, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('job position'),
                'breadcrumbs' => $this->getBreadcrumbs($jobPosition),
                'pageHead'    => [
                    'title'    => $jobPosition->name,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('edit job position'),
                            'fields' => [
                                'code' => [
                                    'type'      => 'input',
                                    'label'     => __('code'),
                                    'required'  => true,
                                    'value'     => $jobPosition->code
                                ],
                                'name' => [
                                    'type'      => 'input',
                                    'label'     => __('name'),
                                    'required'  => true,
                                    'value'     => $jobPosition->name
                                ],
                                'department' => [
                                    'type'        => 'select',
                                    'label'       => __('department'),
                                    'options'     => Options::forModels(ProductCategory::class, label: 'name', value: 'name'),
                                    'placeholder' => __('Select a department'),
                                    'mode'        => 'single',
                                    'value'       => $jobPosition->department
                                ]
                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.job-position.update',
                            'parameters' => $jobPosition->slug

                        ],
                    ]

                ],

            ]
        );
    }

    public function getBreadcrumbs(JobPosition $jobPosition): array
    {
        return ShowJobPosition::make()->getBreadcrumbs([
            'organisation' => $this->organisation->slug,
            'job_position' => $jobPosition->slug
        ], suffix: '('.__('editing').')');
    }
}
