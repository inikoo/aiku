<?php

namespace App\Actions\Fulfilment\Setting;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\Setting\FulfilmentDashboardTabsEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentSettingDashboard extends OrgAction
{
    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Mail/MailDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'        => __('setting'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-ballot'],
                        'title' => __('setting')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('setting')
                    ],
                    'title' => __('setting dashboard'),
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentDashboardTabsEnum::navigation()
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.fulfilments.show.setting.dashboard' =>
               array_merge(
                   ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                   [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.show.setting.dashboard',
                                    'parameters' => $routeParameters
                                ],
                                'label' => __('Setting')
                            ]
                        ]
                    ]
               ),
            default => []
        };
    }


}
