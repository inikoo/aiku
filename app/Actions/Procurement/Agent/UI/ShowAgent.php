<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Agent $agent
 */
class ShowAgent extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(AgentTabsEnum::values());
        $this->agent    = $agent;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Procurement/Agent',
            [
                'title'       => __('agent'),
                'breadcrumbs' => $this->getBreadcrumbs($this->agent),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->agent->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('supplier|suppliers', $this->agent->stats->number_active_suppliers),
                            'number'   => $this->agent->stats->number_active_suppliers,
                            'href'     => [
                                'procurement.agents.show.suppliers.index',
                                $this->agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('suppliers')
                            ]
                        ],
                        // TODO ShowSupplierProducts
                        [
                            'name'     => trans_choice('supplier|suppliers', $this->agent->stats->number_active_suppliers),
                            'number'   => $this->agent->stats->number_active_suppliers,
                            'href'     => [
                                'procurement.agents.show.suppliers.index',
                                $this->agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('suppliers')
                            ]
                        ]
                    ]

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => AgentTabsEnum::navigation()
                ],
                AgentTabsEnum::SUPPLIERS->value => $this->tab == AgentTabsEnum::SUPPLIERS->value ?
                    fn () => SupplierResource::collection(IndexSuppliers::run($this->agent))
                    : Inertia::lazy(fn () => SupplierResource::collection(IndexSuppliers::run($this->agent))),

                AgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == AgentTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->agent))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->agent))),

            ]
        )->table(IndexSuppliers::make()->tableStructure($this->agent))
            ->table(IndexSupplierProducts::make()->tableStructure($this->agent));
    }


     public function jsonResponse(): AgentResource
     {
         return new AgentResource($this->agent);
     }

    public function getBreadcrumbs(Agent $agent, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'procurement.agents.index',
                            ],
                            'label' => __('agent')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.agents.show',
                                'parameters' => [$agent->slug]
                            ],
                            'label' => $agent->code,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ]
        );
    }
}
