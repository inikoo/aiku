<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailroom\ShowMailroom;
use App\Actions\UI\Mail\MailHub;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use App\Models\Marketing\Shop;
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
        $this->canEdit = $request->user()->can('mail.edit');
        return $request->user()->hasPermissionTo("mail.view");
    }

    public function inTenant(Outbox $outbox, ActionRequest $request): Outbox
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ActionRequest $request): Outbox
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInShop(Mailroom $mailroom, Outbox $outbox, ActionRequest $request): Outbox
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
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $outbox),
                'pageHead'    => [
                    'icon'    => 'fal fa-agent',
                    'title'   => $outbox->slug,
                    'edit'    => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
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

    public function getBreadcrumbs(string $routeName, Outbox $outbox): array
    {
        $headCrumb = function (array $routeParameters = []) use ($outbox, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $outbox->id,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('outbox list')
                    ],
                    'modelLabel' => [
                        'label' => __('outbox')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'mail.outboxes.show' => array_merge(
                (new MailHub())->getBreadcrumbs(),
                $headCrumb([$outbox->slug])
            ),
            'mail.mailrooms.show.outboxes.show' => array_merge(
                (new ShowMailroom())->getBreadcrumbs($this->outbox),
                $headCrumb([$outbox->mailroom-> $outbox->slug])
            ),
            default => []
        };
    }
}
