<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditMarketplaceAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

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
                'title'       => __('edit marketplace agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title'    => $agent->code,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [


                        [
                            'title'  => __('ID/contact details'),
                            'icon'   => ['fal', 'fa-address-book'],
                            'fields' => [
                                'code'         => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $agent->code
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company_name'),
                                    'value' => $agent->company_name
                                ],
                                'email'        => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => $agent->email,
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'address'      => [
                                    'type'    => 'address',
                                    'label'   => __('Address'),
                                    'value'   => AddressResource::make($agent->getAddress())->getArray(),
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
                                    'placeholder' => 'Select a Currency',
                                    'options'     => GetCurrenciesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],

                                'default_product_country_origin' => [
                                    'type'        => 'select',
                                    'label'       => __("Product's country of origin"),
                                    'placeholder' => 'Select a Country',
                                    'options'     => GetCountriesOptions::run(),
                                    'mode'        => 'single'
                                ],
                            ]
                        ],
                        [
                            'title'     => __('delete'),
                            'icon'      => 'fa-light fa-trash-alt',
                            'operation' => [
                                [
                                    'component' => 'removeModelAction',
                                    'data'      => RemoveMarketplaceAgent::make()->getAction(
                                        route:[
                                            'name'       => 'models.marketplace-agent.delete',
                                            'parameters' => array_values($this->originalParameters)
                                        ]
                                    )
                                ],


                            ]
                        ],

                    ],

                    'args' => [
                        'updateRoute' => [
                            'name'       => 'models.marketplace-agent.update',
                            'parameters' => $agent->slug

                        ],
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowMarketplaceAgent::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }
}
