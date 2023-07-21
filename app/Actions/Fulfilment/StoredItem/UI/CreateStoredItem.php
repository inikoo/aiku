<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\GetLocationsOptions;
use App\Actions\UI\Fulfilment\FulfilmentDashboard;
use App\Http\Resources\Fulfilment\StoredItemResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStoredItem extends InertiaAction
{
    public function handle($prefix=null): Response
    {
        return $this->htmlResponse();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('fulfilment.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $employees): AnonymousResourceCollection
    {
        return StoredItemResource::collection($employees);
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('stored items'),
                'pageHead'    => [
                    'title'  => __('stored items'),
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'fields' => [
                                'slug' => [
                                    'type'    => 'input',
                                    'label'   => __('slug'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                                'type' => [
                                    'type'    => 'input',
                                    'label'   => __('type'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                                'location' => [
                                    'type'        => 'select',
                                    'label'       => __("Location"),
                                    'placeholder' => 'Select a Location',
                                    'options'     => GetLocationsOptions::run(),
                                    'mode'        => 'single',
                                    'searchable'  => true,
                                    'required'=> true
                                ],
                                'notes' => [
                                    'type'    => 'input',
                                    'label'   => __('notes'),
                                    'value'   => ''
                                ]
                            ]
                        ]
                    ],
                    'route' => [
                        'name' => 'models.agent.store',
                    ]
                ],
            ]
        );
    }

    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new FulfilmentDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'fulfilment.stored-items.index'
                        ],
                        'label' => __('stored items'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
