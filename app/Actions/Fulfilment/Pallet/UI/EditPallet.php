<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditPallet extends OrgAction
{
    private Warehouse|Organisation|FulfilmentCustomer|Fulfilment $parent;
    public function handle(Pallet $storedItem): Pallet
    {
        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.edit");
            return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.view");
        } elseif ($this->parent instanceof Warehouse) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.stored-items.edit");
            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.stored-items.view");
        }

        $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->organisation->id}.stored-items.edit");
        return $request->user()->hasPermissionTo("fulfilment.{$this->organisation->id}.stored-items.view");
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return PalletResource::collection($storedItems);
    }


    public function htmlResponse(Pallet $storedItem, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('stored items'),
                'pageHead'    => [
                    'title'     => __('stored items'),
                    // 'actions'   => [
                    //     [
                    //         'type'  => 'button',
                    //         'style' => 'exitEdit',
                    //         'route' => [
                    //             'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                    //             'parameters' => array_values($request->route()->originalParameters())
                    //         ]
                    //     ]
                    // ]
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
                                    'value'   => $storedItem->reference,
                                    'required'=> true
                                ],
                                'customer_reference' => [
                                    'type'    => 'input',
                                    'label'   => __('customer_reference'),
                                    'value'   => $storedItem->customer_reference,
                                    'required'=> true
                                ],
                                'notes' => [
                                    'type'    => 'input',
                                    'label'   => __('notes'),
                                    'value'   => $storedItem->notes,
                                    'required'=> true
                                ],
                                // 'type' => [
                                //     'type'    => 'select',
                                //     'label'   => __('type'),
                                //     'value'   => $storedItem->type,
                                //     'required'=> true,
                                //     'options' => PalletTypeEnum::values()
                                // ],
                                // 'location' => [
                                //     'type'     => 'combobox',
                                //     'label'    => __('location'),
                                //     'value'    => '',
                                //     'required' => true,
                                //     'apiUrl'   => route('grp.json.locations') . '?filter[slug]=',
                                // ]
                            ]
                        ]
                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'       => 'grp.models.pallet.update',
                            'parameters' => $storedItem->id
                        ],
                    ]
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, Pallet $storedItem, ActionRequest $request): Pallet
    {
        $this->initialisation($organisation, $request);

        return $this->handle($storedItem);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($pallet);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowPallet::make()->getBreadcrumbs(
            $this->parent,
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }
}
