<?php

 namespace App\Actions\Mail\Outbox\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\UI\Dropshipping\AssetsTabsEnum;
use App\Enums\UI\Mail\OutboxDashboardTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOutboxDashboard extends OrgAction
{
    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Mail/OutboxDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('outboxes'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-ballot'],
                        'title' => __('outbox')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('outbox')
                    ],
                    'title' => __('outbox dashboard'),
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => OutboxDashboardTabsEnum::navigation()
                ],


            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
           array_merge(
               ShowShop::make()->getBreadcrumbs($routeParameters),
               [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.outbox.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Outbox')
                        ]
                    ]
                ]
           );
    }


}
