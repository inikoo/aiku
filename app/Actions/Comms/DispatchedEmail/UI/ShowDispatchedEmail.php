<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:08:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\DispatchedEmail\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Comms\DispatchedEmail;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property DispatchedEmail $dispatchedEmail
 */
class ShowDispatchedEmail extends InertiaAction
{
    //use HasUIDispatchedEmail;
    public function handle(DispatchedEmail $dispatchedEmail): DispatchedEmail
    {
        return $dispatchedEmail;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return $request->user()->hasPermissionTo('marketing.view');
    }

    public function inOrganisation(DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    public function inMailshotInShop(Mailshot $mailshot, ActionRequest $request): DispatchedEmail
    {
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    public function inOutboxInShop(Outbox $outbox, ActionRequest $request): DispatchedEmail
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }



    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoomInShop(Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoomInOutboxInMailshotInShop(PostRoom $postRoom, Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoomInMailshotInShop(PostRoom $postRoom, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    public function htmlResponse(DispatchedEmail $dispatchedEmail, ActionRequest $request): Response
    {

        return Inertia::render(
            'Mail/DispatchedEmail',
            [
                'title'       => $dispatchedEmail->id,
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $dispatchedEmail),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $dispatchedEmail->id,

                ],
                'dispatched emails' => $dispatchedEmail
            ]
        );
    }


    public function jsonResponse(DispatchedEmail $dispatchedEmail): DispatchedEmailResource
    {
        return new DispatchedEmailResource($dispatchedEmail);
    }
}
