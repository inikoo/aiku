<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveMarketplaceAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialisation($request);

        return $this->handle($agent);
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete marketplace agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title' => $agent->name,

                    'exitEdit' => [
                        'label' => __('Cancel'),
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],
                'actions'     =>
                    [
                        [
                            'type'        => 'softDelete',
                            'buttonLabel' => __('Bin Agent'),
                            'route'       => [
                                'name'       => 'models.marketplace-agent.delete',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]

            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowAgent::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
