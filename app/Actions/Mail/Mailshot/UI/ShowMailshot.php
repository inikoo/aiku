<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:36:41 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\UI\Inventory\LocationTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Mailshot $mailshot
 */
class ShowMailshot extends OrgAction
{
    use HasUIMailshots;
    use HaCatalogueAuthorisation;

    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }

    public function inOrganisation(Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOutbox(Outbox $outbox, Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoom(PostRoom $postRoom, Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request);
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

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => LocationTabsEnum::navigation()

                ],
                'mailshot' => $mailshot
            ]
        );
    }


    #[Pure] public function jsonResponse(Mailshot $mailshot): MailshotResource
    {
        return new MailshotResource($mailshot);
    }
}
