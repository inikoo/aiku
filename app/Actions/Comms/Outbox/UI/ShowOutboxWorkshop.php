<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Email;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Outbox;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOutboxWorkshop extends OrgAction
{
    use WithActionButtons;


    public function handle(Email $email): Email
    {
        return $email;
    }


    public function asController(Organisation $organisation, Shop $shop, Outbox $outbox, ActionRequest $request): Email
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox->emailOngoingRun->email);
    }

    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, Outbox $outbox, ActionRequest $request): Email
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox->emailOngoingRun->email);
    }

    public function htmlResponse(Email $email, ActionRequest $request): Response
    {
        // dd($email->snapshot->layout);
        return Inertia::render(
            'Org/Web/Workshop/Outbox/OutboxWorkshop', //NEED VUE FILE
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                // 'navigation'  => [
                //     'previous' => $this->getPrevious($emailTemplate, $request),
                //     'next'     => $this->getNext($emailTemplate, $request),
                // ],
                'title'       => $email->subject,
                'pageHead'    => [
                    'title'     => $email->subject,
                    'icon'      => [
                        'tooltip' => __('mailshot'),
                        'icon'    => 'fal fa-mail-bulk'
                    ],
                    // 'iconRight' => $emailTemplate->state->stateIcon()[$emailTemplate->state->value],
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
                            'label' => __('Exit workshop'),
                            'route' => [
                                'name'       => preg_replace('/workshop$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters()),
                            ]
                        ],
                        [
                            'type'  => 'button',
                            'style' => 'save',
                            'label' => __('publish'),
                            'route' => [
                                'name'       => 'grp.models.email-templates.content.publish',
                                'parameters' => $email->id,
                                'method'     => 'post'
                            ]
                        ],
                    ]

                ],
                'snapshot'          => $email->snapshot,
                'builder'           => $email->builder,
                'imagesUploadRoute'   => [
                    'name'       => 'grp.models.email-templates.images.store',
                    'parameters' => $email->id
                ],
                'updateRoute'         => [
                    'name'       => 'grp.models.snapshot.update',
                    'parameters' => $email->snapshot->id
                ],
               /*  'publishRoute'           => [
                    'name'       => 'grp.models.email-templates.content.publish',
                    'parameters' => $email->id
                ], */
                'loadRoute'           => [
                    'name'       => 'grp.models.email-templates.content.show',
                    'parameters' => $email->id
                ],
                'apiKey'            => [
                    'client_id'     => $email->group->settings['beefree']['client_id'],
                    'client_secret' => $email->group->settings['beefree']['client_secret'],
                    'grant_type'    => $email->group->settings['beefree']['grant_type'],
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Email $email, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __($email->subject)
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        /** @var Outbox $outbox */
        $outbox = Outbox::firstWhere('slug', $routeParameters['outbox']);

        return match ($routeName) {
            'org.crm.shop.prospects.mailshots.workshop', 'grp.org.shops.show.comms.outboxes.workshop' =>
            array_merge(
                ShowOutbox::make()->getBreadcrumbs(
                    'grp.org.shops.show.comms.outboxes.workshop',
                    $routeParameters
                ),
                $headCrumb(
                    'modelWithIndex',
                    $outbox->emailOngoingRun->email,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.comms.outboxes.show',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.comms.outboxes.workshop',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


    // public function getPrevious(EmailTemplate $emailTemplate, ActionRequest $request): ?array
    // {
    //     $previous = EmailTemplate::where('slug', '<', $emailTemplate->slug)->orderBy('slug')->first();

    //     return $this->getNavigation($previous, $request->route()->getName());
    // }

    // public function getNext(EmailTemplate $emailTemplate, ActionRequest $request): ?array
    // {
    //     $next = EmailTemplate::where('slug', '>', $emailTemplate->slug)->orderBy('slug')->first();

    //     return $this->getNavigation($next, $request->route()->getName());
    // }

    // private function getNavigation(?EmailTemplate $emailTemplate, string $routeName): ?array
    // {
    //     if (!$emailTemplate) {
    //         return null;
    //     }


    //     return match ($routeName) {
    //         'org.crm.shop.prospects.mailshots.workshop' => [
    //             'label' => $emailTemplate->slug,
    //             'route' => [
    //                 'name'       => $routeName,
    //                 'parameters' => [
    //                     $emailTemplate->scope->slug,
    //                     $emailTemplate->slug
    //                 ]
    //             ]
    //         ],
    //     };
    // }

}
