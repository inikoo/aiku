<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Agent $agent
 */
class ShowMarketplaceAgent extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(AgentTabsEnum::values());
        $this->agent    = $agent;
    }

    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Procurement/MarketplaceAgent',
            [
                'title'       => __('marketplace agent'),
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
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => AgentTabsEnum::navigation()
                ],
            ]
        );
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
                                'name' => 'procurement.marketplace-agents.index',
                            ],
                            'label' => __('marketplace agents')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.marketplace-agents.show',
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
