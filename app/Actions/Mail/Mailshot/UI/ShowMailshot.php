<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:36:41 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Mailshot $mailshot
 */
class ShowMailshot extends InertiaAction
{
    //use HasUIMailshot;
    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return $request->user()->hasPermissionTo('marketing.view');
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
    public function inPostRoomInOutboxInShop(PostRoom $postRoom, Outbox $outbox, Mailshot $mailshot, ActionRequest $request): Mailshot
    {

        $this->initialisation($request);
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
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $mailshot),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $mailshot->id,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,

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
