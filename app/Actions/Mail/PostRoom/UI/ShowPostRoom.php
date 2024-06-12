<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:36:14 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\PostRoom\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Marketing\MarketingHub;
use App\Http\Resources\Mail\PostRoomResource;
use App\Models\Mail\PostRoom;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property PostRoom $postRoom
 */
class ShowPostRoom extends InertiaAction
{
    public function handle(PostRoom $postRoom): PostRoom
    {
        return $postRoom;
    }
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('marketing.view');
    }

    public function inOrganisation(PostRoom $postRoom): void
    {
        $this->postRoom    = $postRoom;
    }

    public function inShop(PostRoom $postRoom, ActionRequest $request): PostRoom
    {
        $this->initialisation($request);
        return $this->handle($postRoom);
    }

    public function htmlResponse(): Response
    {

        return Inertia::render(
            'Mail/PostRoom',
            [
                'title'       => __('post room'),
                'breadcrumbs' => $this->getBreadcrumbs($this->postRoom),
                'pageHead'    => [
                    'icon'  => 'fal fa-cash-register',
                    'title' => $this->postRoom->code,
                    'meta'  => [
                        [
                            'name'     => trans_choice('outbox | outboxes', $this->postRoom->stats->id),
                            'number'   => $this->postRoom->stats->id,
                            'href'     => [
                                'mail.post_rooms.show.outboxes.index',
                                $this->postRoom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-money-check-alt',
                                'tooltip' => __('outboxes')
                            ]
                        ],
                        [
                            'name'     => trans_choice('mailshot | mailshots', $this->postRoom->stats->number_mailshots),
                            'number'   => $this->postRoom->stats->number_mailshots,
                            'href'     => [
                                'mail.post_rooms.show.mailshots.index',
                                $this->postRoom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('mailshots')
                            ]
                        ],
                        [
                            'name'     => trans_choice('dispatched email | dispatched emails', $this->postRoom->stats->number_dispatched_emails),
                            'number'   => $this->postRoom->stats->number_dispatched_emails,
                            'href'     => [
                                'mail.post_rooms.show.dispatched-emails.index',
                                $this->postRoom->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('dispatched emails')
                            ]
                        ]

                    ]

                ],
                'post_room'   => $this->postRoom
            ]
        );
    }


    #[Pure] public function jsonResponse(): PostRoomResource
    {
        return new PostRoomResource($this->postRoom);
    }


    public function getBreadcrumbs(PostRoom $postRoom): array
    {
        return array_merge(
            (new MarketingHub())->getBreadcrumbs(),
            [
                'mail.post_rooms.show' => [
                    'route'           => 'mail.post_rooms.show',
                    'routeParameters' => $postRoom->id,
                    'name'            => $postRoom->code,
                    'index'           => [
                        'route'   => 'mail.post_rooms.index',
                        'overlay' => __('post rooms list')
                    ],
                    'modelLabel'      => [
                        'label' => __('post room')
                    ],
                ],
            ]
        );
    }
}
