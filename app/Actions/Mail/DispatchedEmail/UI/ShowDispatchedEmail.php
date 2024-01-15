<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 15 Jan 2024 13:09:13 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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
    public function inMailroomInShop(Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInOutboxInMailshotInShop(Mailroom $mailroom, Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInMailshotInShop(Mailroom $mailroom, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {

        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    public function htmlResponse(DispatchedEmail $dispatchedEmail): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Mail/DispatchedEmail',
            [
                'title'       => __($dispatchedEmail->id),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $dispatchedEmail),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $dispatchedEmail->id,

                ],
                'dispatched emails' => $dispatchedEmail
            ]
        );
    }


    #[Pure] public function jsonResponse(DispatchedEmail $dispatchedEmail): DispatchedEmailResource
    {
        return new DispatchedEmailResource($dispatchedEmail);
    }
}
