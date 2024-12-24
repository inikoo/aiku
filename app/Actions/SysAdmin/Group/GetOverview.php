<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\OverviewResource;
use App\Models\SysAdmin\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOverview extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(Group $group): AnonymousResourceCollection
    {
        $sections = $this->getSection($group);

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

    public function getSection(Group $group): array
    {
        $section = [
            'Sysadmin'          => [
                [
                    'name'  => 'Changelog',
                    'icon'  => 'fal fa-history',
                    'route' => '',
                    'count' => $group->sysadminStats->number_audits ?? 0
                ],
                [
                    'name'  => 'Users',
                    'icon'  => 'fal fa-users',
                    'route' => '',
                    'count' => $group->sysadminStats->number_users ?? 0
                ],
                [
                    'name'  => 'Guests',
                    'icon'  => 'fal fa-user-alien',
                    'route' => '', // http://app.aiku.test/sysadmin/users?tab=guests
                    'count' => $group->sysadminStats->number_guests ?? 0
                ],
                [
                    'name'  => 'User requests',
                    'icon'  => 'fal fa-road',
                    'route' => '', // http://app.aiku.test/sysadmin/users?tab=users_requests
                    'count' => $group->sysadminStats->number_user_requests ?? 0
                ],
            ],
            'Comms & marketing' => [
                [
                    'name'  => 'Post Rooms',
                    'icon'  => 'fal fa-booth-curtain',
                    'route' => 'grp.overview.comms.post-rooms.index',
                    'count' => $group->commsStats->number_post_rooms ?? 0
                ],
                [
                    'name'  => 'Outboxes',
                    'icon'  => 'fal fa-inbox-out',
                    'route' => 'grp.overview.comms.outboxes-rooms.index',
                    'count' => $group->commsStats->number_outboxes ?? 0
                ],
                [
                    'name'  => 'Newsletters',
                    'icon'  => 'fal fa-newspaper',
                    'route' => 'grp.overview.marketing.newsletters.index',
                    'count' => $group->commsStats->number_mailshots_type_newsletter ?? 0
                ],
                [
                    'name'  => 'Marketing mailshots',
                    'icon'  => 'fal fa-mail-bulk',
                    'route' => 'grp.overview.marketing.mailshots.index',//todo change to correct route
                    'count' => $group->commsStats->number_mailshots_type_marketing ?? 0
                ],
                [
                    'name'  => 'Prospects mailshots',
                    'icon'  => 'fal fa-phone-volume',
                    'route' => 'grp.overview.marketing.mailshots.index',//todo change to correct route
                    'count' => $group->commsStats->number_mailshots_type_invite ?? 0
                ],
                [
                    'name'  => 'Abandoned cart mailshots',
                    'icon'  => 'fal fa-scroll-old',
                    'route' => 'grp.overview.marketing.mailshots.index',//todo change to correct route
                    'count' => $group->commsStats->number_mailshots_type_abandoned_cart ?? 0
                ],
                [
                    'name'  => 'Email Bulk Runs',
                    'icon'  => 'fal fa-raygun',
                    'route' => '',
                    'count' => $group->commsStats->number_bulk_runs ?? 0
                ],
                [
                    'name'  => 'Email Addresses',
                    'icon'  => 'fal fa-envelope',
                    'route' => '',
                    'count' => $group->commsStats->number_email_addresses ?? 0
                ],
                [
                    'name'  => 'Dispatched Emails',
                    'icon'  => 'fal fa-paper-plane',
                    'route' => '',
                    'count' => $group->commsStats->number_dispatched_emails ?? 0
                ],

            ],
            'Catalogue'         => [
                [
                    'name'  => 'Departments',
                    'icon'  => 'fal fa-folder-tree',
                    'route' => 'grp.overview.catalogue.departments.index',
                    'count' => $group->catalogueStats->number_departments ?? 0
                ],
                [
                    'name'  => 'Families',
                    'icon'  => 'fal fa-folder',
                    'route' => 'grp.overview.catalogue.families.index',
                    'count' => $group->catalogueStats->number_families ?? 0
                ],
                [
                    'name'  => 'Products',
                    'icon'  => 'fal fa-boxes',
                    'route' => 'grp.overview.catalogue.products.index',
                    'count' => $group->catalogueStats->number_products ?? 0
                ],
                [
                    'name'  => 'Collections',
                    'icon'  => 'fal fa-album-collection',
                    'route' => 'grp.overview.catalogue.collections.index',
                    'count' => $group->catalogueStats->number_collections ?? 0
                ],
            ],
            'Billables'         => [
                // [
                //     'name' => 'Shipping',
                //     'icon' => 'fal fa-shipping-fast',
                //     'route' => 'grp.overview.billables.shipping.index',
                //     'count' => $group->catalogueStats->number_shipping_zone_schemas ?? 0
                // ],
                [
                    'name'  => 'Rentals',
                    'icon'  => 'fal fa-garage',
                    'route' => '',
                    'count' => $group->catalogueStats->number_rentals ?? 0
                ],
                [
                    'name'  => 'Charges',
                    'icon'  => 'fal fa-charging-station',
                    'route' => 'grp.overview.billables.charges.index',
                    'count' => $group->catalogueStats->number_assets_type_charge ?? 0
                ],
                [
                    'name'  => 'Services',
                    'icon'  => 'fal fa-concierge-bell',
                    'route' => 'grp.overview.billables.services.index',
                    'count' => $group->catalogueStats->number_services ?? 0
                ],
            ],
            'Offer'             => [
                [
                    'name'  => 'Campaigns',
                    'icon'  => 'fal fa-comment-dollar',
                    'route' => 'grp.overview.offer.campaigns.index',
                    'count' => $group->discountsStats->number_offer_campaigns ?? 0
                ],
                [
                    'name'  => 'Offers',
                    'icon'  => 'fal fa-badge-percent',
                    'route' => 'grp.overview.offer.offers.index',
                    'count' => $group->discountsStats->number_offers ?? 0
                ],
            ],
            'Web'               => [
                [
                    'name'  => 'Websites',
                    'icon'  => 'fal fa-globe',
                    'route' => '',
                    'count' => $group->webStats->number_websites ?? 0
                ],
                [
                    'name'  => 'Webpages',
                    'icon'  => 'fal fa-browser',
                    'route' => 'grp.overview.web.webpages.index',
                    'count' => $group->webStats->number_webpages ?? 0
                ],
                [
                    'name'  => 'Banners',
                    'icon'  => 'fal fa-sign',
                    'route' => 'grp.overview.web.banners.index',
                    'count' => $group->webStats->number_banners ?? 0
                ],
            ],
            'CRM'               => [
                [
                    'name'  => 'Customers',
                    'icon'  => 'fal fa-user',
                    'route' => 'grp.overview.crm.customers.index',
                    'count' => $group->crmStats->number_customers ?? 0
                ],
                [
                    'name'  => 'Prospects',
                    'icon'  => 'fal fa-user-plus',
                    'route' => 'grp.overview.crm.prospects.index',
                    'count' => $group->crmStats->number_prospects ?? 0
                ],
                [
                    'name'  => 'Web Users',
                    'icon'  => 'fal fa-user-circle',
                    'route' => 'grp.overview.crm.web-users.index',
                    'count' => $group->crmStats->number_web_users ?? 0
                ],
            ],
            'Ordering'          => [
                [
                    'name'  => 'Orders',
                    'icon'  => 'fal fa-shopping-cart',
                    'route' => 'grp.overview.order.orders.index',
                    'count' => $group->orderingStats->number_orders ?? 0
                ],
                [
                    'name'  => 'Purges',
                    'icon'  => 'fal fa-trash-alt',
                    'route' => 'grp.overview.order.purges.index',
                    'count' => $group->orderingStats->number_purges ?? 0
                ],
                [
                    'name'  => 'Invoices',
                    'icon'  => 'fal fa-file-invoice-dollar',
                    'route' => 'grp.overview.accounting.invoices.index',
                    'count' => $group->accountingStats->number_invoices ?? 0
                ],
                [
                    'name'  => 'Delivery Notes',
                    'icon'  => 'fal fa-truck',
                    'route' => 'grp.overview.order.orders.index',
                    'count' => $group->orderingStats->number_delivery_notes ?? 0
                ],
                [
                    'name'  => 'Transactions',
                    'icon'  => 'fal fa-exchange-alt',
                    'route' => '',
                    'count' => $group->orderingStats->number_invoice_transactions ?? 0
                ],
            ],
            'Inventory'         => [
                [
                    'name'  => 'Stocks',
                    'icon'  => 'fal fa-inventory',
                    'route' => '',
                    'count' => $group->goodsStats->number_stocks ?? 0
                ],
                [
                    'name'  => 'Org Stocks',
                    'icon'  => 'fal fa-warehouse',
                    'route' => '',
                    'count' => $group->inventoryStats->number_org_stocks ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Stock Families',
                    'icon'  => 'fal fa-box',
                    'route' => '',
                    'count' => $group->goodsStats->number_stock_families ?? 0
                ],
                [
                    'name'  => 'Org Stock Families',
                    'icon'  => 'fal fa-boxes-alt',
                    'route' => '',
                    'count' => $group->inventoryStats->number_org_stock_families ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Org Stock Movements',
                    'icon'  => 'fal fa-dolly',
                    'route' => '',
                    'count' => $group->inventoryStats->number_org_stock_movements ?? 0 // need hydrator
                ],
                [
                    'name'  => 'Warehouses',
                    'icon'  => 'fal fa-warehouse-alt',
                    'route' => '',
                    'count' => $group->inventoryStats->number_warehouses ?? 0
                ],
                [
                    'name'  => 'Warehouses Areas',
                    'icon'  => 'fal fa-industry-alt',
                    'route' => '',
                    'count' => $group->inventoryStats->number_warehouse_areas ?? 0
                ],
                [
                    'name'  => 'Locations',
                    'icon'  => 'fal fa-location-arrow',
                    'route' => '',
                    'count' => $group->inventoryStats->number_locations ?? 0
                ],
            ],
            'Fulfilment'        => [
                [
                    'name'  => 'Pallets',
                    'icon'  => 'fal fa-pallet',
                    'route' => '',
                    'count' => $group->fulfilmentStats->number_pallets ?? 0
                ],
                [
                    'name'  => 'Stored Items',
                    'icon'  => 'fal fa-box-open',
                    'route' => '',
                    'count' => $group->fulfilmentStats->number_stored_items ?? 0
                ],
                [
                    'name'  => 'Stock Deliveries',
                    'icon'  => 'fal fa-truck-loading',
                    'route' => '',
                    'count' => $group->procurementStats->number_stock_deliveries ?? 0 // hydrator ?? 0
                ],
                [
                    'name'  => 'Pallet Deliveries',
                    'icon'  => 'fal fa-pallet-alt',
                    'route' => '',
                    'count' => $group->fulfilmentStats->number_pallet_deliveries ?? 0
                ],
                [
                    'name'  => 'Pallet Returns',
                    'icon'  => 'fal fa-forklift',
                    'route' => '',
                    'count' => $group->fulfilmentStats->number_pallet_returns ?? 0
                ],
            ],
            'Procurement'       => [
                [
                    'name'  => 'Agents',
                    'icon'  => 'fal fa-people-arrows',
                    'route' => 'grp.overview.procurement.agents.index',
                    'count' => $group->supplyChainStats->number_agents ?? 0
                ],
                [
                    'name'  => 'Suppliers',
                    'icon'  => 'fal fa-person-dolly',
                    'route' => 'grp.overview.procurement.suppliers.index',
                    'count' => $group->supplyChainStats->number_suppliers ?? 0
                ],
                [
                    'name'  => 'Supplier Products',
                    'icon'  => 'fal fa-users-class',
                    'route' => 'grp.overview.procurement.supplier-products.index',
                    'count' => $group->supplyChainStats->number_supplier_products ?? 0
                ],
                [
                    'name'  => 'Purchase Orders',
                    'icon'  => 'fal fa-clipboard-list',
                    'route' => 'grp.overview.procurement.purchase-orders.index',
                    'count' => $group->supplyChainStats->number_purchase_orders ?? 0
                ],
            ],
            'Accounting'        => [
                [
                    'name'  => 'Accounts',
                    'icon'  => 'fal fa-money-check-alt',
                    'route' => 'grp.overview.accounting.payment-accounts.index',
                    'count' => $group->accountingStats->number_payment_accounts ?? 0
                ],
                [
                    'name'  => 'Payments',
                    'icon'  => 'fal fa-coin',
                    'route' => 'grp.overview.accounting.payments.index',
                    'count' => $group->accountingStats->number_payments ?? 0
                ],
                [
                    'name'  => 'Customer Balances',
                    'icon'  => 'fal fa-piggy-bank',
                    'route' => 'grp.overview.accounting.customer-balances.index',
                    'count' => $group->accountingStats->number_customers_with_balances ?? 0 // need stats for this
                ],
            ],
            'Human Resources'   => [
                [
                    'name'  => 'Workplaces',
                    'icon'  => 'fal fa-building',
                    'route' => 'grp.overview.human-resources.workplaces.index',
                    'count' => $group->humanResourcesStats->number_workplaces ?? 0
                ],
                [
                    'name'  => 'Responsibilities',
                    'icon'  => 'fal fa-clipboard-list-check',
                    'route' => 'grp.overview.human-resources.responsibilities.index',
                    'count' => $group->humanResourcesStats->number_job_positions ?? 0
                ],
                [
                    'name'  => 'Employees',
                    'icon'  => 'fal fa-user-hard-hat',
                    'route' => 'grp.overview.accounting.employees.index',
                    'count' => $group->humanResourcesStats->number_employees ?? 0
                ],
                [
                    'name'  => 'Clocking Machines',
                    'icon'  => 'fal fa-chess-clock',
                    'route' => 'grp.overview.accounting.clocking-machines.index',
                    'count' => $group->humanResourcesStats->number_clocking_machines ?? 0
                ],
                [
                    'name'  => 'Timesheets',
                    'icon'  => 'fal fa-stopwatch',
                    'route' => 'grp.overview.accounting.timesheets.index',
                    'count' => $group->humanResourcesStats->number_timesheets ?? 0 // need hydrator
                ],
            ],
        ];

        return $section;
    }
}
