<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\Comms\Mailshot\UI\IndexMailshots;
use App\Actions\OrgAction;
use App\Actions\Web\HasWorkshopAction;
use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Enums\UI\Mail\OutboxTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\OutboxesResource;
use App\Models\Catalogue\Shop;
use App\Models\Comms\EmailOngoingRun;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Outbox $outbox
 */
class ShowOutbox extends OrgAction
{
    use HasWorkshopAction;

    public function handle(Outbox $outbox): Outbox
    {
        return $outbox;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasAnyPermission([
            'shop-admin.'.$this->shop->id,
            'marketing.'.$this->shop->id.'.view',
            'web.'.$this->shop->id.'.view',
            'orders.'.$this->shop->id.'.view',
            'crm.'.$this->shop->id.'.view',
        ]);
    }

    public function inOrganisation(Organisation $organisation, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisation($organisation, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisationFromShop($shop, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisationFromShop($shop, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }


    public function htmlResponse(Outbox $outbox, ActionRequest $request): Response
    {
        $navigation = OutboxTabsEnum::navigation();
        if (!$outbox->model_type != 'Mailshot') {
            unset($navigation[OutboxTabsEnum::MAILSHOTS->value]);
        }

        if ($outbox->state == OutboxStateEnum::IN_PROCESS) {

            if ($outbox->type == OutboxTypeEnum::USER_NOTIFICATION) {
                dd('Error this should be set up already in the seeder');
            }


            return Inertia::render(
                'Mail/Outbox',
                [
                    'title'       => __('Set up your outbox'),
                    'breadcrumbs' => $this->getBreadcrumbs(
                        $request->route()->getName(),
                        $request->route()->originalParameters()
                    ),
                    'pageHead'    => [
                        'title'   => $outbox->name,
                        'icon'    =>
                            [
                                'icon'  => ['fal', 'fa-inbox-out'],
                                'title' => __('outbox')
                            ],

                    ],
                    'tabs'        => [
                        'current'    => $this->tab,
                        'navigation' => $navigation
                    ],


                    OutboxTabsEnum::SHOWCASE->value => $this->tab == OutboxTabsEnum::SHOWCASE->value ?
                        fn () => GetOutboxShowcase::run($outbox)
                        : Inertia::lazy(fn () => GetOutboxShowcase::run($outbox)),


                ]
            );


        }




        $this->canEdit = true;
        $actions       = $this->workshopActions($request);

        if ($outbox->type === OutboxTypeEnum::USER_NOTIFICATION && $outbox->builder !== EmailBuilderEnum::BLADE->value && $outbox->model_type === class_basename(EmailOngoingRun::class)) {
            $actions = array_merge($actions, $this->canEdit ? [
                'type'  => 'button',
                'style' => 'secondary',
                'label' => __('workshop'),
                'icon'  => ["fal", "fa-drafting-compass"],
                'route' => [
                    'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                    'parameters' => array_values($request->route()->originalParameters())
                ]
            ] : []);
        }

        return Inertia::render(
            'Mail/Outbox',
            [
                'title'       => __('outbox'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($outbox, $request),
                    'next'     => $this->getNext($outbox, $request),
                ],
                'pageHead'    => [
                    'title'   => $outbox->name,
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-inbox-out'],
                            'title' => __('outbox')
                        ],
                    'actions' => $actions,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => OutboxTabsEnum::navigation()
                ],


                OutboxTabsEnum::SHOWCASE->value => $this->tab == OutboxTabsEnum::SHOWCASE->value ?
                    fn () => GetOutboxShowcase::run($outbox)
                    : Inertia::lazy(fn () => GetOutboxShowcase::run($outbox)),

                    OutboxTabsEnum::MAILSHOTS->value => $this->tab == OutboxTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($outbox))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($outbox))),



            ]
        )->table(IndexMailshots::make()->tableStructure(parent:$outbox, prefix: OutboxTabsEnum::MAILSHOTS->value));
    }


    public function jsonResponse(Outbox $outbox): OutboxesResource
    {
        return new OutboxesResource($outbox);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Outbox $outbox, array $routeParameters, $suffix = null) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => $outbox->code,
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $outbox = Outbox::where('slug', $routeParameters['outbox'])->first();

        return match ($routeName) {
            'grp.org.shops.show.comms.outboxes.show' =>
            array_merge(
                IndexOutboxes::make()->getBreadcrumbs('grp.org.shops.show.comms.outboxes.index', $routeParameters),
                $headCrumb(
                    $outbox,
                    [

                        'name'       => 'grp.org.shops.show.comms.outboxes.show',
                        'parameters' => $routeParameters

                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.web.websites.outboxes.show' =>
            array_merge(
                IndexOutboxes::make()->getBreadcrumbs('grp.org.shops.show.web.websites.outboxes', $routeParameters),
                $headCrumb(
                    $outbox,
                    [

                        'name'       => 'grp.org.shops.show.web.websites.outboxes.show',
                        'parameters' => $routeParameters

                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Outbox $outbox, ActionRequest $request): ?array
    {
        $previous = Outbox::where('slug', '<', $outbox->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Outbox $outbox, ActionRequest $request): ?array
    {
        $next = Outbox::where('slug', '>', $outbox->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Outbox $outbox, string $routeName): ?array
    {
        if (!$outbox) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.comms.outboxes.show' => [
                'label' => $outbox->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'shop'         => $outbox->shop->slug,
                        'outbox'       => $outbox->slug
                    ]

                ]
            ],
            'grp.org.shops.show.web.websites.outboxes.show' => [
                'label' => $outbox->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'shop'         => $outbox->shop->slug,
                        'website'      => $outbox->website->slug,
                        'outbox'       => $outbox->slug
                    ]

                ]
            ],
        };
    }


}
