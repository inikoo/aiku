<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 15 Jan 2024 13:12:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailroom\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Marketing\MarketingHub;
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
    public function handle(Mailroom $mailroom): Mailroom
    {
        return $mailroom;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('marketing.view');
    }

    public function inOrganisation(Mailroom $mailroom): void
    {
        $this->mailroom    = $mailroom;
    }

    public function inShop(Mailroom $mailroom, ActionRequest $request): Mailroom
    {
        $this->initialisation($request);
        return $this->handle($mailroom);
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
            (new MarketingHub())->getBreadcrumbs(),
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
