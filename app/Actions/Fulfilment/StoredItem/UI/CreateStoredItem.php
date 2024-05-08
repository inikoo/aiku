<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStoredItem extends OrgAction
{
    public function handle($prefix=null): Response
    {
        return $this->htmlResponse();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view")
            );
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return StoredItemResource::collection($storedItems);
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
                            'title'  => __('Item'),
                            'icon'   => ['fal', 'fa-narwhal'],
                            'fields' => [
                                'reference' => [
                                    'type'    => 'input',
                                    'label'   => __('reference'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                                'type' => [
                                    'type'    => 'select',
                                    'label'   => __('type'),
                                    'value'   => '',
                                    'required'=> true,
                                    'options' => StoredItemTypeEnum::values()
                                ],
                                'location' => [
                                    'type'     => 'combobox',
                                    'label'    => __('location'),
                                    'value'    => '',
                                    'required' => true,
                                    'apiUrl'   => route('grp.json.locations') . '?filter[slug]=',
                                ],
                            ]
                        ]
                    ],
                    'route' => [
                        'name'      => 'grp.models.stored-items.store',
                        'arguments' => array_values(request()->route()->originalParameters())
                    ]
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle();
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return [];
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.fulfilment.stored-items.index'
                        ],
                        'label' => __('stored items'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
