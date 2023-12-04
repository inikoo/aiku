<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use App\Models\Procurement\Supplier;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveSupplier extends InertiaAction
{
    public function handle(Supplier $supplier): Supplier
    {
        return $supplier;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function asController(Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->initialisation($request);

        return $this->handle($supplier);
    }

    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Supplier'),
            'text'        => __("This action will delete this supplier and all it's products"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Supplier $supplier, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete supplier'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('supplier')
                        ],
                    'title'  => $supplier->name,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:[
                        'name'       => 'grp.models.supplier.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )




            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowSupplier::make()->getBreadcrumbs(
            routeName: preg_replace('/remove$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
