<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Models\Procurement\Supplier;

trait HasUISupplier
{
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
