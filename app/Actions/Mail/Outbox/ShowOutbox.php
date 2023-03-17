<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property PaymentAccount $paymentAccount
 */
class ShowOutbox extends InertiaAction
{
    //use HasUIOutbox;
    public function handle(Outbox $outbox): Outbox
    {
        return $outbox;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('mail.edit');
        return $request->user()->hasPermissionTo("mail.view");
    }

    public function asController(Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroom(Mailroom $mailroom, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle($outbox);
    }

    public function htmlResponse(Outbox $outbox): Response
    {
        return Inertia::render(
            'Mail/Outbox',
            [
                'title'       => $outbox->name,
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName),
                'pageHead'    => [
                    'icon'    => 'fal fa-agent',
                    'title'   => $outbox->slug,

                ],
                'outboxes' => $outbox
            ]
        );
    }


    public function jsonResponse(Outbox $outbox): OutboxResource
    {
        return new OutboxResource($outbox);
    }
}
