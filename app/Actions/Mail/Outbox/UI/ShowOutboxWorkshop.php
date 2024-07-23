<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 13:51:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\CRM\Prospect\UI\IndexProspects;
use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Models\Catalogue\Shop;
use App\Models\Mail\EmailTemplate;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOutboxWorkshop extends OrgAction
{
    use WithActionButtons;


    public function handle(EmailTemplate $emailTemplate): EmailTemplate
    {
        return $emailTemplate;
    }


    public function asController(Organisation $organisation, Shop $shop, Outbox $outbox, ActionRequest $request): EmailTemplate
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox->emailTemplate);
    }

    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, Outbox $outbox, ActionRequest $request): EmailTemplate
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($outbox->emailTemplate);
    }

    public function htmlResponse(EmailTemplate $emailTemplate, ActionRequest $request): Response
    {
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
                'title'       => $emailTemplate->title,
                'pageHead'    => [
                    'title'     => $emailTemplate->title,
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
                    ]

                ],
                'emailTemplate'    => EmailTemplateResource::make($emailTemplate)->getArray(),

                'imagesUploadRoute'   => [
                    'name'       => 'org.models.email-templates.images.store',
                    'parameters' => $emailTemplate->id
                ],
                'updateRoute'         => [
                    'name'       => 'org.models.email-templates.content.update',
                    'parameters' => $emailTemplate->id
                ],
                'publishRoute'           => [
                    'name'       => 'org.models.email-templates.content.publish',
                    'parameters' => $emailTemplate->id
                ],
                'loadRoute'           => [
                    'name'       => 'org.models.email-templates.content.show',
                    'parameters' => $emailTemplate->id
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, EmailTemplate $emailTemplate, array $routeParameters, string $suffix = null) {
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
                            'label' => $emailTemplate->title,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $emailTemplate->title
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
                    EmailTemplate::firstWhere('slug', $routeParameters['mailshot']),
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
