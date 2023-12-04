<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Models\Procurement\Agent;
use Inertia\Inertia;
use App\Actions\InertiaAction;
use Lorisleiva\Actions\ActionRequest;
use Inertia\Response;

class RemoveAgent extends InertiaAction
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

    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete agent'),
            'text'        => __("This action will delete this agent and all it's suppliers & products"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'  => $agent->name,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:[
                        'name'       => 'grp.models.agent.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
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
