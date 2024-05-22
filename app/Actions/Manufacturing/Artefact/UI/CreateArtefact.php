<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Artefact\UI;

use App\Actions\OrgAction;
use App\Enums\Manufacturing\Artefact\ArtefactStateEnum;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateArtefact extends OrgAction
{
    protected Production|Organisation $parent;

    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('new artefact'),
                'pageHead'    => [
                    'title'        => __('new artefact'),
                    'icon'         => [
                        'title' => __('create artefact'),
                        'icon'  => 'fal fa-industry'
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.productions.show.crafts.artefacts.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('create artefact'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'state' => [
                                    'type'     => 'select',
                                    'options'  => ArtefactStateEnum::values(),
                                    'label'    => __('state'),
                                    'value'    => '',
                                    'required' => false
                                ],
                            ]
                        ]
                    ],
                    'route'      => [
                        'name'       => 'grp.models.production.artefacts.store',
                        'parameters' => [$this->parent->id]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

            return $request->user()->hasAnyPermission(
                [
                    'productions-view.'.$this->organisation->id,
                    'org-supervisor.'.$this->organisation->id
                ]
            );
        }

        $this->canEdit = $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.edit");

        return $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.view");
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): Response
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): Response
    {
        $this->parent = $production;
        $this->initialisationFromProduction($production, $request);

        return $this->handle($request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexArtefacts::make()->getBreadcrumbs(request()->route()->getName(), $routeParameters),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('Creating Artefact'),
                    ]
                ]
            ]
        );
    }
}
