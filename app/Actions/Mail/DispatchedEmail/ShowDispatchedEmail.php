<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\PostRoom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
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
