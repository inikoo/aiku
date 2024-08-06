<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 10:36:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\UI\Marketing;

use App\Actions\InertiaAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Models\Mail\Outbox;
use App\Models\Catalogue\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Outbox $outbox
 * @property \App\Models\SysAdmin\User $user
 */
class MarketingHub extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('marketing.view');
    }


    public function inOrganisation(ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);

        return $request;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->originalParameters()();


        return Inertia::render(
            'Mail/MailHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('marketing'),
                'pageHead'    => [
                    'title' => __('marketing'),
                ],


                'flatTreeMaps' => match ($routeName) {
                    'shops.show.mail.hub' =>
                    [
                        [
                            [
                                'name'  => __('post room'),
                                'icon'  => ['fal', 'fa-mailbox'],
                                'href'  => ['grp.marketing.post_rooms.index'],
                                'index' => [
                                    'number' => $routeParameters['shop']
                                ]

                            ],
                            [
                                'name'  => __('outboxes'),
                                'icon'  => ['fal', 'fa-inbox-out'],
                                'href'  => ['grp.marketing.outboxes.index'],
                                'index' => [
                                    'number' => $this->outbox
                                ]

                            ],
                            [
                                'name'  => __('mailshots'),
                                'icon'  => ['fal', 'fa-mail-bulk'],
                                'href'  => ['grp.marketing.mailshots.index'],
                                'index' => [
                                    'number' => $this->outbox

                                ]

                            ],
                            [
                                'name'  => __('dispatched emails'),
                                'icon'  => ['fal', 'fa-envelope'],
                                'href'  => ['grp.marketing.dispatched-emails.index'],
                                'index' => [
                                    'number' => $this->outbox
                                ]

                            ],
                        ]


                    ],
                    default => [
                        [
                            [
                                'name'  => __('post room'),
                                'icon'  => ['fal', 'fa-mailbox'],
                                'href'  => ['grp.marketing.post_rooms.index'],
                                'index' => [
                                    'number' => $this->outbox
                                ]

                            ],
                            [
                                'name'  => __('outboxes'),
                                'icon'  => ['fal', 'fa-inbox-out'],
                                'href'  => ['grp.marketing.outboxes.index'],
                                'index' => [
                                    'number' => $this->outbox
                                ]

                            ],
                            [
                                'name'  => __('mailshots'),
                                'icon'  => ['fal', 'fa-mail-bulk'],
                                'href'  => ['grp.marketing.mailshots.index'],
                                'index' => [
                                    'number' => $this->outbox

                                ]

                            ],
                            [
                                'name'  => __('dispatched emails'),
                                'icon'  => ['fal', 'fa-envelope'],
                                'href'  => ['grp.marketing.dispatched-emails.index'],
                                'index' => [
                                    'number' => $this->outbox
                                ]

                            ],
                        ]


                    ]
                }


            ]
        );
    }

    public function getBreadcrumbs($routeName, $routeParameters): array
    {
        return match ($routeName) {
            'grp.marketing.hub' => array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.org.shops.show.marketing.dashboard'
                            ],
                            'label' => __('marketing').' ('.__('all shops').')',
                            'icon'  => 'fal fa-bullhorn'
                        ],
                    ],
                ]
            ),
            'grp.marketing.shops.show.hub', 'grp.org.shops.show.marketing.mailshots.index' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.marketing.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('marketing').' ('.$routeParameters['shop'].')',
                            'icon'  => 'fal fa-bullhorn'
                        ],
                    ],
                ]
            ),
            default => []
        };
    }
}
