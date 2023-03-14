<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Agent $agent
 */
class ShowAgent extends InertiaAction
{
    use HasUIAgent;
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): void
    {
        $this->initialisation($request);
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
                'agent'   => $this->agent
            ]
        );
    }


    #[Pure] public function jsonResponse(): AgentResource
    {
        return new AgentResource($this->agent);
    }



}
