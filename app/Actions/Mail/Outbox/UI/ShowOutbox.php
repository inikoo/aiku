<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:35:44 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Outbox $outbox
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
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return $request->user()->hasPermissionTo('marketing.view');
    }

    public function inOrganisation(Outbox $outbox, ActionRequest $request): Outbox
    {

        $this->initialisation($request);
        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoom(PostRoom $postRoom, Outbox $outbox, ActionRequest $request): Outbox
    {

        $this->initialisation($request);
        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ActionRequest $request): Outbox
    {

        $this->initialisation($request);
        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPostRoomInShop(PostRoom $postRoom, Outbox $outbox, ActionRequest $request): Outbox
    {

        $this->initialisation($request);
        return $this->handle($outbox);
    }

    public function htmlResponse(Outbox $outbox): Response
    {
        return Inertia::render(
            'Mail/Outbox',
            [
                'title'       => $outbox->name,
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $outbox),
                'pageHead'    => [
                    'icon'    => 'fal fa-agent',
                    'title'   => $outbox->slug,
                    'edit'    => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
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