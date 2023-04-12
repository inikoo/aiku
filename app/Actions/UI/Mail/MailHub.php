<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 10:36:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\UI\Mail;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Models\Mail\Outbox;
use App\Models\Marketing\Shop;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Outbox $outbox
 * @property User $user
 */
class MailHub extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("mail.view");
    }


    public function inTenant(ActionRequest $request): ActionRequest
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
        return Inertia::render(
            'Mail/MailHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters()
                ),
                'title'       => __('mail'),
                'pageHead'    => [
                    'title' => __('mail'),
                ],

                'flatTreeMaps' => [
                    [
                        [// TODO Check why it gives error putting de ->stats->//whatever//
                         'name'  => __('mailroom'),
                         'icon'  => ['fal', 'fa-cash-register'],
                         'href'  => ['mail.mailrooms.index'],
                         'index' => [
                             'number' => $this->outbox
                         ]

                        ],
                        [
                            'name'  => __('outboxes'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'href'  => ['mail.outboxes.index'],
                            'index' => [
                                'number' => $this->outbox
                            ]

                        ],
                        [
                            'name'  => __('mailshots'),
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'href'  => ['mail.mailshots.index'],
                            'index' => [
                                'number' => $this->outbox

                            ]

                        ],
                        [
                            'name'  => __('dispatched emails'),
                            'icon'  => ['fal', 'fa-coins'],
                            'href'  => ['mail.dispatched-emails.index'],
                            'index' => [
                                'number' => $this->outbox
                            ]

                        ],

                    ],

                ]

            ]
        );
    }

    public function getBreadcrumbs($routeName, $routeParameters): array
    {
        $headCrumb = function (array $route) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $route,
                        'label' => __('mailroom'),
                        'icon'  => 'fal fa-mail-bulk'
                    ],
                ],
            ];
        };


        return match ($routeName) {
            'mail.hub' => array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'      => 'mail.hub',
                        'parameters'=> []
                    ]
                )
            ),
            'shops.show.mail.hub' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb(
                    [
                        'name'      => 'shops.show.mail.hub',
                        'parameters'=> $routeParameters['shop']->slug
                    ]
                )
            ),
            default => []
        };
    }
}
