<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
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
        $this->canEdit = $request->user()->can('mail.edit');
        return $request->user()->hasPermissionTo("mail.view");
    }

    public function inTenant(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        //$this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOutbox(Outbox $outbox, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInOutboxInShop(Mailroom $mailroom, Outbox $outbox, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroom(Mailroom $mailroom, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    public function htmlResponse(Mailshot $mailshot): Response
    {
        //$this->validateAttributes();


        return Inertia::render(
            'Mail/Mailshot',
            [
                'title'       => __($mailshot->id),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $mailshot),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $mailshot->id,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
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
