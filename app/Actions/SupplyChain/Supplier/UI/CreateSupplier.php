<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Jan 2024 09:36:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\UI;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\HasSupplyChainFields;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateSupplier extends GrpAction
{
    use HasSupplyChainFields;

    public function htmlResponse(ActionRequest $request): Response
    {

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('new supplier'),
                'pageHead'    => [
                    'title'        => __('new supplier'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.supply-chain.suppliers.index',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => $this->supplyChainFields(),
                    'route'     => [
                        'name' => 'grp.models.supplier.store',
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('supply-chain.edit');
    }

    public function asController(ActionRequest $request): ActionRequest
    {
        $this->initialisation(group(), $request);

        return $request;
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexSuppliers::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating supplier"),
                    ]
                ]
            ]
        );
    }
}
