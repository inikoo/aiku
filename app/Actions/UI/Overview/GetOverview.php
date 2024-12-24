<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\UI\Overview;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\OverviewResource;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOverview extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(Organisation $organisation): AnonymousResourceCollection
    {
        $sections = $this->getSection($organisation);

        $dataRaw = collect($sections)->map(function ($data, $section) {
            return (object)[
                'section' => $section,
                'data'    => collect($data)->map(function ($item) {
                    return (object)$item;
                }),
            ];
        });

        return OverviewResource::collection($dataRaw);
    }

    public function getSection(Organisation $organisation): array
    {
        $section = [
            'Sysadmin'          => [
                [
                    'name'  => 'Changelog',
                    'icon'  => 'fal fa-history',
                    'route' => '',
                    'count' => $organisation->stats->number_audits ?? 0
                ],
            ],
            'Comms & marketing' => [
                [
                    'name'  => 'Post Rooms',
                    'icon'  => 'fal fa-booth-curtain',
                    'route' => '',
                    'count' => $organisation->commsStats->number_org_post_rooms ?? 0
                ],
                [
                    'name'  => 'Outboxes',
                    'icon'  => 'fal fa-inbox-out',
                    'route' => '',
                    'count' => $organisation->commsStats->number_outboxes ?? 0
                ],
                [
                    'name'  => 'Newsletters',
                    'icon'  => 'fal fa-newspaper',
                    'route' => '',
                    'count' => $organisation->commsStats->number_mailshots_type_newsletter ?? 0
                ],
                [
                    'name'  => 'Marketing mailshots',
                    'icon'  => 'fal fa-mail-bulk',
                    'route' => '',
                    'count' => $organisation->commsStats->number_mailshots_type_marketing ?? 0
                ],
                [
                    'name'  => 'Prospects mailshots',
                    'icon'  => 'fal fa-phone-volume',
                    'route' => '',
                    'count' => $organisation->commsStats->number_mailshots_type_invite ?? 0
                ],
                [
                    'name'  => 'Abandoned cart mailshots',
                    'icon'  => 'fal fa-scroll-old',
                    'route' => '',
                    'count' => $organisation->commsStats->number_mailshots_type_abandoned_cart ?? 0
                ],
                [
                    'name'  => 'Email Bulk Runs',
                    'icon'  => 'fal fa-raygun',
                    'route' => '',
                    'count' => $organisation->commsStats->number_email_bulk_runs ?? 0
                ],
                [
                    'name'  => 'Email Addresses',
                    'icon'  => 'fal fa-envelope',
                    'route' => '',
                    'count' => $organisation->commsStats->number_email_addresses ?? 0
                ],
                [
                    'name'  => 'Dispatched Emails',
                    'icon'  => 'fal fa-paper-plane',
                    'route' => '',
                    'count' => $organisation->commsStats->number_dispatched_emails ?? 0
                ],

            ],
            'Catalogue'         => [
                [
                    'name'  => 'Departments',
                    'icon'  => 'fal fa-folder-tree',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_departments ?? 0
                ],
                [
                    'name'  => 'Families',
                    'icon'  => 'fal fa-folder',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_families ?? 0
                ],
                [
                    'name'  => 'Products',
                    'icon'  => 'fal fa-boxes',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_products ?? 0
                ],
                [
                    'name'  => 'Collections',
                    'icon'  => 'fal fa-album-collection',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_collections ?? 0
                ],
            ],
            'Billables'         => [
                // [
                //     'name' => 'Shipping',
                //     'icon' => 'fal fa-shipping-fast',
                //     'route' => '',
                //     'count' => $organisation->catalogueStats->number_shipping_zone_schemas ?? 0
                // ],
                [
                    'name'  => 'Rentals',
                    'icon'  => 'fal fa-garage',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_rentals ?? 0
                ],
                [
                    'name'  => 'Charges',
                    'icon'  => 'fal fa-charging-station',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_assets_type_charge ?? 0
                ],
                [
                    'name'  => 'Services',
                    'icon'  => 'fal fa-concierge-bell',
                    'route' => '',
                    'count' => $organisation->catalogueStats->number_services ?? 0
                ],
            ],
            'Offer'             => [
                [
                    'name'  => 'Campaigns',
                    'icon'  => 'fal fa-comment-dollar',
                    'route' => '',
                    'count' => $organisation->discountsStats->number_offer_campaigns ?? 0
                ],
                [
                    'name'  => 'Offers',
                    'icon'  => 'fal fa-badge-percent',
                    'route' => '',
                    'count' => $organisation->discountsStats->number_offers ?? 0
                ],
            ],
            'Web'               => [
                [
                    'name'  => 'Websites',
                    'icon'  => 'fal fa-globe',
                    'route' => '',
                    'count' => $organisation->webStats->number_websites ?? 0
                ],
                [
                    'name'  => 'Webpages',
                    'icon'  => 'fal fa-browser',
                    'route' => '',
                    'count' => $organisation->webStats->number_webpages ?? 0
                ],
                [
                    'name'  => 'Banners',
                    'icon'  => 'fal fa-sign',
                    'route' => '',
                    'count' => $organisation->webStats->number_banners ?? 0
                ],
            ],
            'CRM'               => [
                [
                    'name'  => 'Customers',
                    'icon'  => 'fal fa-user',
                    'route' => '',
                    'count' => $organisation->crmStats->number_customers ?? 0
                ],
                [
                    'name'  => 'Prospects',
                    'icon'  => 'fal fa-user-plus',
                    'route' => '',
                    'count' => $organisation->crmStats->number_prospects ?? 0
                ],
                [
                    'name'  => 'Web Users',
                    'icon'  => 'fal fa-user-circle',
                    'route' => '',
                    'count' => $organisation->crmStats->number_web_users ?? 0
                ],
            ],
            'Ordering'          => [
                [
                    'name'  => 'Orders',
                    'icon'  => 'fal fa-shopping-cart',
                    'route' => '',
                    'count' => $organisation->orderingStats->number_orders ?? 0
                ],
                [
                    'name'  => 'Purges',
                    'icon'  => 'fal fa-trash-alt',
                    'route' => '',
                    'count' => $organisation->orderingStats->number_purges ?? 0
                ],
                [
                    'name'  => 'Invoices',
                    'icon'  => 'fal fa-file-invoice-dollar',
                    'route' => '',
                    'count' => $organisation->accountingStats->number_invoices ?? 0
                ],
                [
                    'name'  => 'Delivery Notes',
                    'icon'  => 'fal fa-truck',
                    'route' => '',
                    'count' => $organisation->orderingStats->number_delivery_notes ?? 0
                ],
                [
                    'name'  => 'Transactions',
                    'icon'  => 'fal fa-exchange-alt',
                    'route' => '',
                    'count' => $organisation->orderingStats->number_invoice_transactions ?? 0
                ],
            ],
            'Inventory'         => [
                [
                    'name'  => 'Org Stocks',
                    'icon'  => 'fal fa-warehouse',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_org_stocks ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Org Stock Families',
                    'icon'  => 'fal fa-boxes-alt',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_org_stock_families ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Org Stock Movements',
                    'icon'  => 'fal fa-dolly',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_org_stock_movements ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Warehouses',
                    'icon'  => 'fal fa-warehouse-alt',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_warehouses ?? 0
                ],
                [
                    'name'  => 'Warehouses Areas',
                    'icon'  => 'fal fa-industry-alt',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_warehouse_areas ?? 0
                ],
                [
                    'name'  => 'Locations',
                    'icon'  => 'fal fa-location-arrow',
                    'route' => '',
                    'count' => $organisation->inventoryStats->number_locations ?? 0
                ],
            ],
            'Fulfilment'        => [
                [
                    'name'  => 'Pallets',
                    'icon'  => 'fal fa-pallet',
                    'route' => '',
                    'count' => $organisation->fulfilmentStats->number_pallets ?? 0
                ],
                [
                    'name'  => 'Stored Items',
                    'icon'  => 'fal fa-box-open',
                    'route' => '',
                    'count' => $organisation->fulfilmentStats->number_stored_items ?? 0
                ],
                [
                    'name'  => 'Stock Deliveries',
                    'icon'  => 'fal fa-truck-loading',
                    'route' => '',
                    'count' => $organisation->procurementStats->number_stock_deliveries ?? 0 // hydrator ?? 0
                ],
                [
                    'name'  => 'Pallet Deliveries',
                    'icon'  => 'fal fa-pallet-alt',
                    'route' => '',
                    'count' => $organisation->fulfilmentStats->number_pallet_deliveries ?? 0
                ],
                [
                    'name'  => 'Pallet Returns',
                    'icon'  => 'fal fa-forklift',
                    'route' => '',
                    'count' => $organisation->fulfilmentStats->number_pallet_returns ?? 0
                ],
            ],
            'Procurement'       => [
                [
                    'name'  => 'Agents',
                    'icon'  => 'fal fa-people-arrows',
                    'route' => '',
                    'count' => $organisation->procurementStats->number_org_agents ?? 0
                ],
                [
                    'name'  => 'Suppliers',
                    'icon'  => 'fal fa-person-dolly',
                    'route' => '',
                    'count' => $organisation->procurementStats->number_org_suppliers ?? 0
                ],
                [
                    'name'  => 'Supplier Products',
                    'icon'  => 'fal fa-users-class',
                    'route' => '',
                    'count' => $organisation->procurementStats->number_org_supplier_products ?? 0
                ],
                [
                    'name'  => 'Purchase Orders',
                    'icon'  => 'fal fa-clipboard-list',
                    'route' => '',
                    'count' => $organisation->procurementStats->number_purchase_orders ?? 0
                ],
            ],
            'Accounting'        => [
                [
                    'name'  => 'Accounts',
                    'icon'  => 'fal fa-money-check-alt',
                    'route' => '',
                    'count' => $organisation->accountingStats->number_payment_accounts ?? 0
                ],
                [
                    'name'  => 'Payments',
                    'icon'  => 'fal fa-coin',
                    'route' => '',
                    'count' => $organisation->accountingStats->number_payments ?? 0
                ],
                [
                    'name'  => 'Customer Balances',
                    'icon'  => 'fal fa-piggy-bank',
                    'route' => '',
                    'count' => $organisation->accountingStats->number_customers_with_balances ?? 0 // need stats for this
                ],
            ],
            'Human Resources'   => [
                [
                    'name'  => 'Workplaces',
                    'icon'  => 'fal fa-building',
                    'route' => '',
                    'count' => $organisation->humanResourcesStats->number_workplaces ?? 0
                ],
                [
                    'name'  => 'Responsibilities',
                    'icon'  => 'fal fa-clipboard-list-check',
                    'route' => '',
                    'count' => $organisation->humanResourcesStats->number_job_positions ?? 0
                ],
                [
                    'name'  => 'Employees',
                    'icon'  => 'fal fa-user-hard-hat',
                    'route' => '',
                    'count' => $organisation->humanResourcesStats->number_employees ?? 0
                ],
                [
                    'name'  => 'Clocking Machines',
                    'icon'  => 'fal fa-chess-clock',
                    'route' => '',
                    'count' => $organisation->humanResourcesStats->number_clocking_machines ?? 0
                ],
                [
                    'name'  => 'Timesheets',
                    'icon'  => 'fal fa-stopwatch',
                    'route' => '',
                    'count' => $organisation->humanResourcesStats->number_timesheets ?? 0 // need hydrator
                ],
            ],
        ];

        return $section;
    }
}
