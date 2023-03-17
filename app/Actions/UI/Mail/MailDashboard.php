<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 10:36:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\UI\Mail;

use App\Actions\InertiaAction;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Outbox $outbox
 * @property User $user
 */
class MailDashboard extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("mail.view");
    }


    public function asController(ActionRequest $request): void
    {
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Mail/MailDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('mail'),
                'pageHead'    => [
                    'title' => __('mail'),
                ],

                'treeMaps'    => [
                    [
                        [
                            'name'  => __('mailroom'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'href'  => ['mailroom.mailrooms.index'],
                            'index' => [
                                'number' => $this->outbox->stats->outbox_id
                            ]

                        ],
                        [
                            'name'  => __('outboxes'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'href'  => ['mailroom.outboxes.index'],
                            'index' => [
                                'number' => $this->outbox->stats->outbox_id
                            ]

                        ],
                        [
                            'name'  => __('mailshots'),
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'href'  => ['mailroom.mailshots.index'],
                            'index' => [
                                'number' => $this->outbox->stats->number_mailshots
                            ]

                        ],
                        [
                            'name'  => __('dispatched emails'),
                            'icon'  => ['fal', 'fa-coins'],
                            'href'  => ['mailroom.dispatched-emails.index'],
                            'index' => [
                                'number' => $this->outbox->stats->number_dispatched_emails
                            ]

                        ],

                    ],

                ]

            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'mail.dashboard' => [
                'route' => 'mail.dashboard',
                'name'  => __('mail'),
            ]
        ];
    }
}
