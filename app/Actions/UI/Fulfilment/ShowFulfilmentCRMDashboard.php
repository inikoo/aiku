<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCRMDashboard extends OrgAction
{
    public function handle(Fulfilment $fulfilment): Fulfilment
    {
        return $fulfilment;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.view");
    }



    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }


    public function htmlResponse(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $container = [
            'icon'    => ['fal', 'fa-pallet-alt'],
            'tooltip' => __('Fulfilment'),
            'label'   => Str::possessive($fulfilment->shop->name)
        ];


        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Org/Fulfilment/CRM/Dashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => 'CRM',
                'pageHead'    => [
                    'title'     => __('customer relationship manager'),
                    'container' => $container
                ],
                'stats'       => [
                    'customers' => [
                        'name' => __('customers'),
                        'stat' => $fulfilment->shop->crmStats->number_customers,

                        'href' =>
                            [
                                'name'       => 'grp.org.fulfilment.crm.customers.index',
                                'parameters' => $routeParameters
                            ]

                    ],
                    'prospects' => [
                        'name' => __('prospects'),
                        'stat' => $fulfilment->shop->crmStats->number_prospects,
                        'href' =>
                            [
                                'name'       => 'grp.org.fulfilment.crm.prospects.index',
                                'parameters' => array_merge($routeParameters, [
                                    '_query' => [
                                        'tab' => 'prospects'
                                    ]
                                ])
                            ]
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {

        $fulfilment=Fulfilment::where('slug', $routeParameters['fulfilment'])->first();

        return array_merge(
            ShowOrganisationDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilment.crm.dashboard',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('CRM').' ('.$fulfilment->shop->code.')',
                    ]
                ]
            ]
        );
    }

}
