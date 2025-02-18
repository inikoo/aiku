<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\Comms\EmailBulkRun\UI\IndexEmailBulkRuns;
use App\Actions\Comms\Mailshot\UI\IndexMailshots;
use App\Actions\OrgAction;
use App\Actions\Web\HasWorkshopAction;
use App\Enums\Comms\Outbox\OutboxBuilderEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\UI\Mail\OutboxTabsEnum;
use App\Http\Resources\Mail\EmailBulkRunsResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\OutboxesResource;
use App\Models\Catalogue\Shop;
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

    /**
     * @var \App\Models\Catalogue\Shop|\App\Models\Fulfilment\Fulfilment|\App\Models\SysAdmin\Organisation
     */
    private Organisation|Fulfilment|Shop $parent;

    public function handle(Outbox $outbox): Outbox
    {
        return $outbox;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Fulfilment) {
            return    $this->canEdit = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }

        return $request->user()->authTo([
            'shop-admin.'.$this->shop->id,
            'marketing.'.$this->shop->id.'.view',
            'web.'.$this->shop->id.'.view',
            'orders.'.$this->shop->id.'.view',
            'crm.'.$this->shop->id.'.view',
        ]);
    }

    public function inOrganisation(Organisation $organisation, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(OutboxTabsEnum::values());

        return $this->handle($outbox);
    }

    public function htmlResponse(Outbox $outbox, ActionRequest $request): Response
    {
        $actions = [];
        if ($outbox->builder !== OutboxBuilderEnum::BLADE) {
            $actions = [
                [
                    'type'  => 'button',
                    'style' => 'secondary',
                    'label' => __('workshop'),
                    'icon'  => 'fal fa-drafting-compass',
                    'route' => [
                        'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                        'parameters' => array_values($request->route()->originalParameters())
                    ]
                ]
            ];
        }

        $navigation = OutboxTabsEnum::navigation();
        if (!$outbox->model_type != 'Mailshot') {
            unset($navigation[OutboxTabsEnum::MAILSHOTS->value]);
        }

        if ($outbox->state == OutboxStateEnum::IN_PROCESS) {
            unset($navigation[OutboxTabsEnum::EMAIL_BULK_RUNS->value]);
        }

        return Inertia::render(
            'Comms/Outbox',
            [
                'title'       => __('Set up your outbox'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'   => $outbox->name,
                    'model'   => __('Outbox'),
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-inbox-out'],
                            'title' => __('outbox')
                        ],
                    'actions' => $actions,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => $navigation
                ],


                OutboxTabsEnum::SHOWCASE->value => $this->tab == OutboxTabsEnum::SHOWCASE->value ?
                    fn () => GetOutboxShowcase::run($outbox)
                    : Inertia::lazy(fn () => GetOutboxShowcase::run($outbox)),

                OutboxTabsEnum::MAILSHOTS->value => $this->tab == OutboxTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($outbox))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($outbox))),

                    OutboxTabsEnum::EMAIL_BULK_RUNS->value => $this->tab == OutboxTabsEnum::EMAIL_BULK_RUNS->value ?
                fn () => EmailBulkRunsResource::collection(IndexEmailBulkRuns::run($outbox))
                : Inertia::lazy(fn () => EmailBulkRunsResource::collection(IndexEmailBulkRuns::run($outbox))),


            ]
        )->table(IndexEmailBulkRuns::make()->tableStructure(parent:$outbox, prefix: OutboxTabsEnum::EMAIL_BULK_RUNS->value))
            ->table(IndexMailshots::make()->tableStructure(parent:$outbox, prefix: OutboxTabsEnum::MAILSHOTS->value));

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
                        'label' => $outbox->name,
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $outbox = Outbox::where('slug', $routeParameters['outbox'])->first();

        return match ($routeName) {
            'grp.org.shops.show.comms.outboxes.show', 'grp.org.shops.show.comms.outboxes.workshop' =>
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
            'grp.org.fulfilments.show.operations.comms.outboxes.show' =>
            array_merge(
                IndexOutboxes::make()->getBreadcrumbs('grp.org.fulfilments.show.operations.comms.outboxes', $routeParameters),
                $headCrumb(
                    $outbox,
                    [

                        'name'       => 'grp.org.fulfilments.show.operations.comms.outboxes.show',
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
            'grp.org.fulfilments.show.operations.comms.outboxes.show' => [
                'label' => $outbox->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'fulfilment'   => $this->fulfilment->slug,
                        'outbox'       => $outbox->slug
                    ]

                ]
            ],
        };
    }


}
