<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailroom;

use App\Actions\InertiaAction;
use App\Actions\UI\Mail\MailDashboard;
use App\Http\Resources\Mail\MailroomResource;
use App\Models\Mail\Mailroom;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Mailroom $mailroom
 */
class ShowMailroom extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("mail.view");
    }

    public function asController(Mailroom $mailroom): void
    {
        $this->mailroom    = $mailroom;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Mail/Mailroom',
            [
                'title'       => __('mailroom'),
                'breadcrumbs' => $this->getBreadcrumbs($this->mailroom),
                'pageHead'    => [
                    'icon'  => 'fal fa-cash-register',
                    'title' => $this->mailroom->code,
                    'meta'  => [
                        [
                            'name'     => trans_choice('outbox | outboxes', $this->mailroom->stats->id),
                            'number'   => $this->mailroom->stats->id,
                            'href'     => [
                                'mail.mailrooms.show.outboxes.index',
                                $this->mailroom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-money-check-alt',
                                'tooltip' => __('outboxes')
                            ]
                        ],
                        [
                            'name'     => trans_choice('mailshot | mailshots', $this->mailroom->stats->number_mailshots),
                            'number'   => $this->mailroom->stats->number_mailshots,
                            'href'     => [
                                'mail.mailrooms.show.mailshots.index',
                                $this->mailroom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('mailshots')
                            ]
                        ],
                        [
                            'name'     => trans_choice('dispatched email | dispatched emails', $this->mailroom->stats->number_dispatched_emails),
                            'number'   => $this->mailroom->stats->number_dispatched_emails,
                            'href'     => [
                                'mail.mailrooms.show.dispatched-emails.index',
                                $this->mailroom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('dispatched emails')
                            ]
                        ]

                    ]

                ],
                'mailroom'   => $this->mailroom
            ]
        );
    }


    #[Pure] public function jsonResponse(): MailroomResource
    {
        return new MailroomResource($this->mailroom);
    }


    public function getBreadcrumbs(Mailroom $mailroom): array
    {
        return array_merge(
            (new MailDashboard())->getBreadcrumbs(),
            [
                'mail.mailrooms.show' => [
                    'route'           => 'mail.mailrooms.show',
                    'routeParameters' => $mailroom->id,
                    'name'            => $mailroom->code,
                    'index'           => [
                        'route'   => 'mail.mailrooms.index',
                        'overlay' => __('mailroom list')
                    ],
                    'modelLabel'      => [
                        'label' => __('mailroom')
                    ],
                ],
            ]
        );
    }
}
