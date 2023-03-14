<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 12:34:50 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class ShowSupplier extends InertiaAction
{
    public function handle(Supplier $supplier): Supplier
    {
        return $supplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Supplier $supplier, Request $request): Supplier
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($supplier);
    }

    public function htmlResponse(Supplier $supplier): Response
    {
        return Inertia::render(
            'Procurement/Supplier',
            [
                'title'       => __('supplier'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $supplier),
                'pageHead'    => [
                    'title' => $supplier->name,

                ],
                'supplier'    => new SupplierResource($supplier)
            ]
        );
    }


    #[Pure] public function jsonResponse(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }


    public function getBreadcrumbs(string $routeName, Supplier $supplier): array
    {
        $headCrumb = function (array $routeParameters = []) use ($supplier, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $supplier->code,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('suppliers list')
                    ],
                    'modelLabel'      => [
                        'label' => __('supplier')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'suppliers.show' => array_merge(
                (new ProcurementDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'agent.show.suppliers.show' => array_merge(
                (new ShowAgent())->getBreadcrumbs($supplier->owner),
                $headCrumb([$supplier->owner_type, $supplier->id])
            ),
            default => []
        };
    }
}
