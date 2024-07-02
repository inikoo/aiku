<?php

namespace App\Actions\Mail;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\Mail\MailDashboardTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMailDashboard extends OrgAction
{
    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request);

        return $request;
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): ActionRequest
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
                'title'        => __('mail'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-ballot'],
                        'title' => __('mail')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('mail')
                    ],
                    'title' => __('mail dashboard'),
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => MailDashboardTabsEnum::navigation()
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.shops.show.mail.dashboard' =>
               array_merge(
                   ShowShop::make()->getBreadcrumbs($routeParameters),
                   [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'route' => [
                                    'name'       => 'grp.org.shops.show.mail.dashboard',
                                    'parameters' => $routeParameters
                                ],
                                'label' => __('Mail')
                            ]
                        ]
                    ]
               ),
            'grp.org.fulfilments.show.mail.dashboard' =>
               array_merge(
                   ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                   [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'route' => [
                                    'name'       => 'grp.org.fulfilments.show.mail.dashboard',
                                    'parameters' => $routeParameters
                                ],
                                'label' => __('Mail')
                            ]
                        ]
                    ]
               ),
            default => []
        };
    }


}
