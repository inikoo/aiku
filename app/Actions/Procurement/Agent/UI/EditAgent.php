<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\Assets\Country\GetAddressData;
use App\Actions\InertiaAction;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialisation($request);

        return $this->handle($agent);
    }



    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title'     => $agent->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'   => __('id'),
                            'icon'    => 'fa-light fa-user',
                            'fields'  => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $agent->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $agent->name
                                ],
                            ]
                        ],
                        [
                        'title'   => __('contact'),
                        'icon'    => 'fa-light fa-phone',
                        'fields'  => [
                            'email' => [
                                'type'   => 'input',
                                'label'  => __('email'),
                                'value'  => $agent->email,
                                'options'=> [
                                    'inputType'=> 'email'
                                ]
                            ],
                            'address'        => [
                                'type'    => 'address',
                                'label'   => __('Address'),
                                'value'   => ['countryID' => app('currentTenant')->country_id],
                                'options' => [
                                    'countriesAddressData' => GetAddressData::run()

                                ]
                            ],

                        ]
                    ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.agent.update',
                            'parameters'=> $agent->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowAgent::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }
}
