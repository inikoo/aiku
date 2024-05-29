<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreatePallet extends OrgAction
{
    public function handle(): Response
    {
        return $this->htmlResponse();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.view")
            );
    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletResource::collection($pallets);
    }

    public function htmlResponse(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(request()->route()->getName(), request()->route()->parameters()),
                'title'       => __('pallets'),
                'pageHead'    => [
                    'title'  => __('pallets'),
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
                                'delivery_id' => [
                                    'type'    => 'select',
                                    'label'   => __('pallet delivery'),
                                    'value'   => '',
                                    'required'=> true,
                                    'options' => Options::forModels(PalletDelivery::class)->toArray()
                                ],
                                'type' => [
                                    'type'    => 'select',
                                    'label'   => __('type'),
                                    'value'   => '',
                                    'required'=> true,
                                    'options' => PalletTypeEnum::values()
                                ],
                            ]
                        ]
                    ],
                    'route' => [
                        'name'      => 'grp.org.fulfilments.show.pallets.create',
                        'arguments' => array_values(request()->route()->originalParameters())
                    ]
                ],
            ]
        );
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilments.show.pallets.create',
                            'parameters' => array_values($routeParameters)
                        ],
                        'label' => __('Pallets'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
