<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Actions\Helpers\Country\UI\GetCountriesOptions;
use App\Actions\Helpers\Currency\UI\GetCurrenciesOptions;
use App\Actions\OrgAction;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditOrgAgent extends OrgAction
{
    public function handle(OrgAgent $orgAgent): OrgAgent
    {
        return $orgAgent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->authTo('procurement.edit');

        return $request->user()->authTo("procurement.view");
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): OrgAgent
    {
        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent);
    }

    public function htmlResponse(OrgAgent $orgAgent, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('edit agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($orgAgent, $request),
                    'next'     => $this->getNext($orgAgent, $request),
                ],
                'pageHead'    => [
                    'title'   => $orgAgent->agent->code,
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('ID/contact details '),
                            'icon'   => ['fal', 'fa-address-book'],
                            'fields' => [
                                'code'    => [
                                    'type'  => 'input',
                                    'label' => __('code '),
                                    'value' => $agent->code
                                ],
                                'name'    => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => $agent->organisation->name
                                ],
                                'email'   => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => $agent->organisation->email,
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'address' => [
                                    'type'    => 'address',
                                    'label'   => __('Address'),
                                    'value'   => AddressResource::make($agent->organisation->address)->getArray(),
                                    'options' => [
                                        'countriesAddressData' => GetAddressData::run()

                                    ]
                                ],
                            ]
                        ],
                        [
                            'title'  => __('settings'),
                            'icon'   => 'fa-light fa-cog',
                            'fields' => [

                                'currency_id' => [
                                    'type'        => 'select',
                                    'label'       => __('currency'),
                                    'placeholder' => __('Select a currency'),
                                    'options'     => GetCurrenciesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],

                                'default_product_country_origin' => [
                                    'type'        => 'select',
                                    'label'       => __("Asset's country of origin"),
                                    'placeholder' => __('Select a country'),
                                    'options'     => GetCountriesOptions::run(),
                                    'mode'        => 'single'
                                ],
                            ]
                        ],


                    ],

                    'args' => [
                        'updateRoute' => [
                            'name'       => 'grp.models.marketplace-agent.update',
                            'parameters' => $agent->slug

                        ],
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowOrgAgent::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(Agent $agent, ActionRequest $request): ?array
    {
        $previous = Agent::where('code', '<', $agent->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Agent $agent, ActionRequest $request): ?array
    {
        $next = Agent::where('code', '>', $agent->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Agent $agent, string $routeName): ?array
    {
        if (!$agent) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.org_agents.edit' => [
                'label' => $agent->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent' => $agent->slug
                    ]

                ]
            ]
        };
    }
}
