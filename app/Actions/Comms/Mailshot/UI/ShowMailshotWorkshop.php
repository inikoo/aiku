<?php
/*
 * author Arya Permana - Kirin
 * created on 04-12-2024-15h-10m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\Mailshot\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Http\Resources\Helpers\SnapshotResource;
use App\Models\Catalogue\Shop;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Mailshot;
use App\Models\Helpers\Snapshot;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMailshotWorkshop extends OrgAction
{
    use WithActionButtons;

    private Snapshot $snapshot;

    public function handle(Snapshot $snapshot): Snapshot
    {
        return $snapshot;
    }


    public function asController(Organisation $organisation, Shop $shop, Mailshot $mailshot, ActionRequest $request): Snapshot
    {
        $this->snapshot = $mailshot->email->snapshot;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($this->snapshot);
    }

    public function htmlResponse(Snapshot $snapshot, ActionRequest $request): Response
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
                'title'       => $snapshot->parent->subject,
                'pageHead'    => [
                    'title'     => $snapshot->parent->subject,
                    'icon'      => [
                        'tooltip' => __('snapshot'),
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
                'snapshot'    => SnapshotResource::make($snapshot)->getArray(),

                // 'imagesUploadRoute'   => [
                //     'name'       => 'grp.models.email-templates.images.store',
                //     'parameters' => $emailTemplate->id
                // ],
                // 'updateRoute'         => [
                //     'name'       => 'grp.models.email-templates.content.update',
                //     'parameters' => $emailTemplate->id
                // ],
                // 'publishRoute'           => [
                //     'name'       => 'grp.models.email-templates.content.publish',
                //     'parameters' => $emailTemplate->id
                // ],
                // 'loadRoute'           => [ -> i don't know what kind of data should i give to this route
                //     'name'       => 'grp.models.email-templates.content.show',
                //     'parameters' => $emailTemplate->id
                // ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Snapshot $snapshot, array $routeParameters, string $suffix = null) {
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
                            'label' => $snapshot->parent->subject,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $snapshot->parent->subject
                    ],
                    'suffix'         => $suffix
                ],
            ];
        };


        return match ($routeName) {
            'grp.org.shops.show.marketing.mailshots.workshop' =>
            array_merge(
                ShowMailshot::make()->getBreadcrumbs(
                    'grp.org.shops.show.marketing.mailshots.show',
                    $routeParameters,
                    $this->shop
                ),
                $headCrumb(
                    'modelWithIndex',
                    Snapshot::firstWhere('id', $this->snapshot->id),
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.marketing.mailshots.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.marketing.mailshots.show',
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
