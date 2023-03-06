<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

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
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent): void
    {
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


    public function getBreadcrumbs(Agent $agent): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                'procurement.agents.show' => [
                    'route'           => 'procurement.agents.show',
                    'routeParameters' => $agent->slug,
                    'name'            => $agent->code,
                    'index'           => [
                        'route'   => 'procurement.agents.index',
                        'overlay' => __('agents list')
                    ],
                    'modelLabel'      => [
                        'label' => __('agent')
                    ],
                ],
            ]
        );
    }
}
