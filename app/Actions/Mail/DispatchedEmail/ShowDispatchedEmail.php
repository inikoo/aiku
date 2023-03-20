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
use App\Models\Mail\Mailroom;
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
        $this->canEdit = $request->user()->can('mail.edit');
        return $request->user()->hasPermissionTo("mail.view");
    }

    public function asController(DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        //$this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOutbox(Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInOutbox(Mailroom $mailroom, Outbox $outbox, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($dispatchedEmail);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroom(Mailroom $mailroom, DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
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
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $dispatchedEmail),
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
