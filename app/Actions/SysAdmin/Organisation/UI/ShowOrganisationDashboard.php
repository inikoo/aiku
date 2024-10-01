<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\OrgAction;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowOrganisationDashboard extends OrgAction
{
    use AsAction;

    public function handle(ActionRequest $request): Response
    {
        $organisation = $this->organisation;
        return Inertia::render(
            'Dashboard/OrganisationDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters(), __('Dashboard')),

                'sales_intervals' => [
                    'all' => $organisation->salesIntervals->org_amount_all,
                    'ytd' => $organisation->salesIntervals->org_amount_ytd,
                    'mtd' => $organisation->salesIntervals->org_amount_mtd,
                    'lw'  => $organisation->salesIntervals->org_amount_lw,
                    'lm'  => $organisation->salesIntervals->org_amount_lm,
                    '1w'  => $organisation->salesIntervals->org_amount_1w,
                    '1m'  => $organisation->salesIntervals->org_amount_1m,
                    '1q'  => $organisation->salesIntervals->org_amount_1q,
                    '1y'  => $organisation->salesIntervals->org_amount_1y,
                ],
                'human_resources' => [
                    'job_positions' => $organisation->humanResourcesStats->number_job_positions,
                    'number_workplaces' => $organisation->humanResourcesStats->number_workplaces,
                    'number_clocking_machines' => $organisation->humanResourcesStats->number_clocking_machines,
                    'number_employees'  => $organisation->humanResourcesStats->number_employees,
                    'number_employees_currently_working'    => $organisation->humanResourcesStats->number_employees_currently_working
                ],
                'procurement'   => [
                    'number_org_agents' => $organisation->procurementStats->number_org_agents,
                    'number_org_suppliers' => $organisation->procurementStats->number_org_suppliers,
                    'number_purchase_orders' => $organisation->procurementStats->number_purchase_orders,
                    'number_stock_deliveries' => $organisation->procurementStats->number_stock_deliveries,
                ],
                'inventory' => [
                    'number_warehouses' =>  $organisation->inventoryStats->number_warehouses,
                    'number_locations'  =>  $organisation->inventoryStats->number_locations,
                    'number_empty_locations' => $organisation->inventoryStats->number_empty_locations,
                    'number_org_stocks' => $organisation->inventoryStats->number_org_stocks,
                    'number_deliveries' => $organisation->inventoryStats->number_deliveries
                ],
                'fulfilment'    => [
                    'number_pallets'    => $organisation->fulfilmentStats->number_pallets,
                    'number_stored_items'   => $organisation->fulfilmentStats->number_stored_items,
                    'number_pallet_deliveries'  => $organisation->fulfilmentStats->number_pallet_deliveries,
                    'number_recurring_bills'    => $organisation->fulfilmentStats->number_recurring_bills,
                ],
                'catalogue' => [
                    'number_departments'    => $organisation->catalogueStats->number_departments,
                    'number_collections'    => $organisation->catalogueStats->number_collections,
                    'number_assets'    => $organisation->catalogueStats->number_assets,
                    'number_products'    => $organisation->catalogueStats->number_products,
                    'number_services'    => $organisation->catalogueStats->number_services,
                    'number_subscriptions'    => $organisation->catalogueStats->number_subscriptions,
                    'number_charges'    => $organisation->catalogueStats->number_charges,
                    'number_shipping_zone_schemas'    => $organisation->catalogueStats->number_shipping_zone_schemas,
                    'number_shipping_zones'    => $organisation->catalogueStats->number_shipping_zones,
                    'number_adjustments'    => $organisation->catalogueStats->number_adjustments,
                ],
                'sales' => [
                    'number_orders' => $organisation->catalogueStats->number_orders,
                    'number_invoices'   =>  $organisation->catalogueStats->number_invoices,
                    'number_delivery_notes' => $organisation->catalogueStats->number_delivery_notes
                ]
            ]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }

    public function getBreadcrumbs(array $routeParameters, $label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-tachometer-alt-fast',
                    'label' => $label,
                    'route' => [
                        'name'       => 'grp.org.dashboard.show',
                        'parameters' => $routeParameters
                    ]
                ]

            ],

        ];
    }
}
