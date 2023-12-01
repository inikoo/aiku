<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\InertiaAction;
use App\Models\Mail\Outbox;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditOutbox extends InertiaAction
{
    use HasUIOutbox;

    public function handle(Outbox $outbox): Outbox
    {
        return $outbox;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');

        return $request->user()->hasPermissionTo("mail.edit");
    }

    public function inTenant(Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisation($request);

        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisation($request);

        return $this->handle($outbox);
    }

    public function htmlResponse(Outbox $outbox): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('outbox'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $outbox),
                'pageHead'    => [
                    'title'    => $outbox->name,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters),
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'    => __('contact information'),
                            'fields'   => [

                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
                                    'value' => $outbox->name
                                ],
                                'mailroom_id' => [
                                    'type'  => 'input',
                                    'label' => __('mailroom'),
                                    'value' => $outbox->mailroom_id
                                ],
                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'      => 'models.outbox.update',
                            'parameters'=> $outbox->slug

                        ],
                    ]

                ],

            ]
        );
    }
}
