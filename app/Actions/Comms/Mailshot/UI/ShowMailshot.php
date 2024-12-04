<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use App\Enums\UI\Mail\MailshotTabsEnum;

/**
 * @property Mailshot $mailshot
 */
class ShowMailshot extends OrgAction
{
    use HasUIMailshots;
    use HasCatalogueAuthorisation;

    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }

    public function inOrganisation(Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request)->withTab(MailshotTabsEnum::values());
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOutbox(Outbox $outbox, Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request)->withTab(MailshotTabsEnum::values());
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($shop, $request)->withTab(MailshotTabsEnum::values());

        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoom(PostRoom $postRoom, Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request)->withTab(MailshotTabsEnum::values());
        return $this->handle($mailshot);
    }

    public function htmlResponse(Mailshot $mailshot, ActionRequest $request): Response
    {
        return Inertia::render(
            'Mail/Mailshot',
            [
                'title'       => $mailshot->id,
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters(), $mailshot->shop->organisation),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => 'Mailshot '.$mailshot->id,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
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
                            $this->canEdit ? [
                                'type'  => 'button',
                                'style' => 'edit',
                                'label' => __('settings'),
                                'icon'  => ["fal", "fa-sliders-h"],
                                'route' => [
                                    'name'       => "grp.org.shops.show.comms.outboxes.workshop",
                                    'parameters' => [
                                        $this->organisation->slug, 
                                        $this->shop->slug, 
                                        $mailshot->slug
                                    ]
                                ]
                            ] : []
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => MailshotTabsEnum::navigation()
                ],
                MailshotTabsEnum::SHOWCASE->value => $this->tab == MailshotTabsEnum::SHOWCASE->value ?
                    fn () => GetMailshotShowcase::run($mailshot)
                    : Inertia::lazy(fn () => GetMailshotShowcase::run($mailshot)),

                MailshotTabsEnum::EMAIL_PREVIEW->value => $this->tab == MailshotTabsEnum::EMAIL_PREVIEW->value ?
                    fn () => GetMailshotPreview::run($mailshot)
                    : Inertia::lazy(fn () => GetMailshotPreview::run($mailshot)),
            ]
        );
    }


    #[Pure] public function jsonResponse(Mailshot $mailshot): MailshotResource
    {
        return new MailshotResource($mailshot);
    }
}
