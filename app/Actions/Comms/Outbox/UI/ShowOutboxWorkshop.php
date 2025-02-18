<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Email;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOutboxWorkshop extends OrgAction
{
    use WithActionButtons;


    /**
     * @var \App\Models\Catalogue\Shop|\App\Models\Fulfilment\Fulfilment
     */
    private Fulfilment|Shop $parent;

    public function handle(Outbox $outbox): Email
    {
        if ($outbox->builder == EmailBuilderEnum::BLADE) {
            throw ValidationException::withMessages([
                'value' => 'Builder is not supported'
            ]);
        }

        return $outbox->emailOngoingRun?->email;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(Organisation $organisation, Shop $shop, Outbox $outbox, ActionRequest $request): Email
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, Outbox $outbox, ActionRequest $request): Email
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Outbox $outbox, ActionRequest $request): Email
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($outbox);
    }

    public function htmlResponse(Email $email, ActionRequest $request): Response
    {
        $beeFreeSettings = Arr::get($email->group->settings, 'beefree');

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
                'title' => $email->subject,
                'pageHead' => [
                    'title' => $email->subject,
                    'icon' => [
                        'tooltip' => __('mailshot'),
                        'icon' => 'fal fa-mail-bulk'
                    ],
                    // 'iconRight' => $emailTemplate->state->stateIcon()[$emailTemplate->state->value],
                    'actions' => [
                        [
                            'type' => 'button',
                            'style' => 'exit',
                            'label' => __('Exit workshop'),
                            'route' => [
                                'name' => preg_replace('/workshop$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters()),
                            ]
                        ],
                        [
                            'type' => 'button',
                            'style' => 'exit',
                            'label' => __('Toggle'),
                            'route' => match (class_basename($this->parent)) {
                                Shop::class => [
                                    'method' => 'patch',
                                    'name' => 'grp.models.shop.outboxes.toggle',
                                    'parameters' => [
                                        'shop' => $email->shop_id,
                                        'outbox' => $email->outbox_id
                                    ],
                                ],
                                Fulfilment::class => [
                                    'method' => 'patch',
                                    'name' => 'grp.models.fulfilment.outboxes.toggle',
                                    'parameters' => [
                                        'shop' => $email->shop_id,
                                        'outbox' => $email->outbox_id
                                    ],
                                ],
                                default => []
                            }
                        ],
                    ]

                ],
                /*  'compiled_layout'    => $email->snapshot->compiled_layout, */
                'unpublished_layout' => $email->unpublishedSnapshot->layout,
                'snapshot' => $email->unpublishedSnapshot,
                'builder' => $email->builder,
                'imagesUploadRoute' => [
                    'name' => 'grp.models.email-templates.images.store',
                    'parameters' => $email->id
                ],
                'updateRoute' => [
                    'name' => 'grp.models.shop.outboxes.workshop.update',
                    'parameters' => [
                        'shop' => $email->shop_id,
                        'outbox' => $email->outbox_id
                    ],
                    'method' => 'patch'
                ],
                'loadRoute' => [
                    'name' => 'grp.models.email-templates.content.show',
                    'parameters' => $email->id
                ],
                'publishRoute' => [
                    'name' => 'grp.models.shop.outboxes.publish',
                    'parameters' => [
                        'shop' => $email->shop_id,
                        'outbox' => $email->outbox_id
                    ],
                    'method' => 'post'
                ],
                'sendTestRoute' => [
                    'name' => 'grp.models.shop.outboxes.send.test',
                    'parameters' => [
                        'shop' => $email->shop_id,
                        'outbox' => $email->outbox_id
                    ],
                    'method' => 'post'
                ],
                'status' => $email->outbox->state,
                // 'loadRoute'           => [ -> i don't know what kind of data should i give to this route
                //     'name'       => 'grp.models.email-templates.content.show',
                //     'parameters' => $emailTemplate->id
                // ]
                'apiKey' => [
                    'client_id' => Arr::get($beeFreeSettings, 'client_id'),
                    'client_secret' => Arr::get($beeFreeSettings, 'client_secret'),
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Email $email, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type' => 'simple',
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
                            'name' => 'grp.org.shops.show.comms.outboxes.show',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name' => 'grp.org.shops.show.comms.outboxes.workshop',
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
