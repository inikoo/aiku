<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 13:51:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\InertiaAction;
use App\Actions\Leads\Prospect\UI\IndexProspects;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\Mailshot;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProspectMailshotWorkshop extends InertiaAction
{
    use WithActionButtons;
    public $shop;

    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('crm.prospects.edit');
        $this->canDelete = $request->user()->hasPermissionTo('crm.prospects.edit');

        return
            (
                $request->user()->hasPermissionTo('crm.prospects.view')
            );
    }

    public function asController(Shop $shop, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->initialisation($request);
        $this->shop = $shop;
        return $this->handle($mailshot);
    }


    public function htmlResponse(Mailshot $mailshot, ActionRequest $request): Response
    {
        return Inertia::render(
            'CRM/Prospects/WorkshopMailshot',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($mailshot, $request),
                    'next'     => $this->getNext($mailshot, $request),
                ],
                'title'       => $mailshot->subject,
                'pageHead'    => [
                    'title'     => $mailshot->subject,
                    'icon'      => [
                        'tooltip' => __('mailshot'),
                        'icon'    => 'fal fa-mail-bulk'
                    ],
                    'iconRight' => $mailshot->state->stateIcon()[$mailshot->state->value],
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
                    ]

                ],
                'mailshot'    => MailshotResource::make($mailshot)->getArray(),


                'imagesUploadRoute'   => [
                    'name'       => 'org.models.mailshot.images.store',
                    'parameters' => $mailshot->id
                ],
                'setAsReadyRoute'     => $mailshot->state == MailshotStateEnum::READY
                    ? null
                    : [
                        'name'       => 'org.models.mailshot.state.ready',
                        'parameters' => $mailshot->id
                    ],
                'sendRoute'           => [
                    'name'       => 'org.models.mailshot.send',
                    'parameters' => $mailshot->id
                ],
                'sendTestRoute'           => [
                    'name'       => 'org.models.mailshot.send.test',
                    'parameters' => $mailshot->id
                ],
                'setAsScheduledRoute' => [
                    'name'       => 'org.models.mailshot.state.scheduled',
                    'parameters' => $mailshot->id
                ],
                'updateRoute'         => [
                    'name'       => 'org.models.mailshot.content.update',
                    'parameters' => $mailshot->id
                ],
                'loadRoute'           => [
                    'name'       => 'org.models.mailshot.content.show',
                    'parameters' => $mailshot->id
                ],
                'updateDetailRoute'   => [
                    'name'       => 'org.models.shop.prospect-mailshot.update',
                    'parameters' => [
                        'shop'     => $this->shop->id,
                        'mailshot' => $mailshot->id
                    ],
                ],

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Mailshot $mailshot, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('mailshots')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $mailshot->subject,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $mailshot->subject
                    ],
                    'suffix'         => $suffix
                ],
            ];
        };


        return match ($routeName) {
            'org.crm.shop.prospects.mailshots.workshop' =>
            array_merge(
                IndexProspects::make()->getBreadcrumbs(
                    'org.crm.shop.prospects.index',
                    $routeParameters
                ),
                $headCrumb(
                    'modelWithIndex',
                    Mailshot::firstWhere('slug', $routeParameters['mailshot']),
                    [
                        'index' => [
                            'name'       => 'org.crm.shop.prospects.mailshots.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'org.crm.shop.prospects.mailshots.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


    public function getPrevious(Mailshot $mailshot, ActionRequest $request): ?array
    {
        $previous = Mailshot::where('slug', '<', $mailshot->slug)->orderBy('slug')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Mailshot $mailshot, ActionRequest $request): ?array
    {
        $next = Mailshot::where('slug', '>', $mailshot->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Mailshot $mailshot, string $routeName): ?array
    {
        if (!$mailshot) {
            return null;
        }


        return match ($routeName) {
            'org.crm.shop.prospects.mailshots.workshop' => [
                'label' => $mailshot->slug,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        $mailshot->parent->slug,
                        $mailshot->slug
                    ]
                ]
            ],
        };
    }

}
