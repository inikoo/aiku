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
use App\Models\CRM\Customer;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Group;
use App\Models\Web\Banner;
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
                'data' => collect($data)->map(function ($item) {
                    return (object)$item;
                }),
            ];
        });

        return OverviewResource::collection($dataRaw);
    }

    public function getSection(Group $group): array
    {
        $section = [
            'Comms' => [
                [
                    'name' => 'Post Rooms',
                    'icon' => 'fal fa-inbox-out',
                    'route' => 'grp.overview.comms.post-rooms.index',
                    'count' => $group->commsStats->number_post_rooms
                ],
                [
                    'name' => 'Outboxes',
                    'icon' => 'fal fa-inbox-out',
                    'route' => 'grp.overview.comms.outboxes-rooms.index',
                    'count' => $group->commsStats->number_outboxes
                ],
            ],
            'Catalogue' => [
                [
                    'name' => 'Departments',
                    'icon' => 'fal fa-folder-tree',
                    'route' => 'grp.overview.catalogue.departments.index',
                    'count' => $group->catalogueStats->number_departments
                ],
                [
                    'name' => 'Families',
                    'icon' => 'fal fa-folder',
                    'route' => 'grp.overview.catalogue.families.index',
                    'count' => $group->catalogueStats->number_families
                ],
                [
                    'name' => 'Products',
                    'icon' => 'fal fa-boxes',
                    'route' => 'grp.overview.catalogue.products.index',
                    'count' => $group->catalogueStats->number_products
                ],
                [
                    'name' => 'Collections',
                    'icon' => 'fal fa-album-collection',
                    'route' => 'grp.overview.catalogue.collections.index',
                    'count' => $group->catalogueStats->number_collections
                ],
            ],
            'Billables' => [
                [
                    'name' => 'Shipping',
                    'icon' => 'fal fa-shipping-fast',
                    'route' => 'grp.overview.billables.shipping.index',
                    'count' => $group->catalogueStats->number_shipping_zone_schemas
                ],
                [
                    'name' => 'Charges',
                    'icon' => 'fal fa-charging-station',
                    'route' => 'grp.overview.billables.charges.index',
                    'count' => $group->catalogueStats->number_charges
                ],
                [
                    'name' => 'Services',
                    'icon' => 'fal fa-concierge-bell',
                    'route' => 'grp.overview.billables.services.index',
                    'count' => $group->catalogueStats->number_services
                ],
            ],
            'Offer' => [
                [
                    'name' => 'Campaigns',
                    'icon' => 'fal fa-comment-dollar',
                    'route' => 'grp.overview.offer.campaigns.index',
                    'count' => $group->discountsStats->number_offer_campaigns
                ],
                [
                    'name' => 'Offers',
                    'icon' => 'fal fa-badge-percent',
                    'route' => 'grp.overview.offer.offers.index',
                    'count' => $group->discountsStats->number_offers
                ],
            ],
            'Marketing' => [
                [
                    'name' => 'Newsletters',
                    'icon' => 'fal fa-newspaper',
                    'route' => 'grp.overview.marketing.newsletters.index',
                    'count' => $group->commsStats->number_outboxes_type_newsletter
                ],
                [
                    'name' => 'Mailshots',
                    'icon' => 'fal fa-mail-bulk',
                    'route' => 'grp.overview.marketing.mailshots.index',
                    'count' => $group->commsStats->number_outboxes_type_marketing
                ],
            ],
            'Web' => [
                [
                    'name' => 'Webpages',
                    'icon' => 'fal fa-browser',
                    'route' => 'grp.overview.web.webpages.index',
                    'count' => $group->webStats->number_webpages
                ],
                [
                    'name' => 'Banners',
                    'icon' => 'fal fa-sign',
                    'route' => 'grp.overview.web.banners.index',
                    'count' => Banner::where('group_id', $group->id)->count() // need stats for this
                ],
            ],
            'CRM' => [
                [
                    'name' => 'Customers',
                    'icon' => 'fal fa-user',
                    'route' => 'grp.overview.crm.customers.index',
                    'count' => $group->crmStats->number_customers
                ],
                [
                    'name' => 'Prospects',
                    'icon' => 'fal fa-user-plus',
                    'route' => 'grp.overview.crm.prospects.index',
                    'count' => $group->crmStats->number_prospects
                ],
            ],
            'Order' => [
                [
                    'name' => 'Orders',
                    'icon' => 'fal fa-shopping-cart',
                    'route' => 'grp.overview.order.orders.index',
                    'count' => $group->orderingStats->number_orders
                ],
                [
                    'name' => 'Purges',
                    'icon' => 'fal fa-trash-alt',
                    'route' => 'grp.overview.order.purges.index',
                    'count' => $group->orderingStats->number_purges
                ],
                [
                    'name' => 'Delivery Notes',
                    'icon' => 'fal fa-truck',
                    'route' => 'grp.overview.order.orders.index',
                    'count' => $group->orderingStats->number_delivery_notes
                ],
            ],
            'Procurement' => [
                [
                    'name' => 'Agents',
                    'icon' => 'fal fa-people-arrows',
                    'route' => 'grp.overview.procurement.agents.index',
                    'count' => $group->supplyChainStats->number_agents
                ],
                [
                    'name' => 'Suppliers',
                    'icon' => 'fal fa-person-dolly',
                    'route' => 'grp.overview.procurement.suppliers.index',
                    'count' => $group->supplyChainStats->number_suppliers
                ],
                [
                    'name' => 'Supplier Products',
                    'icon' => 'fal fa-users-class',
                    'route' => 'grp.overview.procurement.supplier-products.index',
                    'count' => $group->supplyChainStats->number_supplier_products
                ],
                [
                    'name' => 'Purchase Orders',
                    'icon' => 'fal fa-clipboard-list',
                    'route' => 'grp.overview.procurement.purchase-orders.index',
                    'count' => $group->supplyChainStats->number_purchase_orders
                ],
            ],
            'Accounting' => [
                [
                    'name' => 'Invoices',
                    'icon' => 'fal fa-file-invoice-dollar',
                    'route' => 'grp.overview.accounting.invoices.index',
                    'count' => $group->accountingStats->number_invoices
                ],
                [
                    'name' => 'Accounts',
                    'icon' => 'fal fa-money-check-alt',
                    'route' => 'grp.overview.accounting.payment-accounts.index',
                    'count' => $group->accountingStats->number_payment_accounts
                ],
                [
                    'name' => 'Payments',
                    'icon' => 'fal fa-coin',
                    'route' => 'grp.overview.accounting.payments.index',
                    'count' => $group->accountingStats->number_payments
                ],
                [
                    'name' => 'Customer Balances',
                    'icon' => 'fal fa-piggy-bank',
                    'route' => 'grp.overview.accounting.customer-balances.index',
                    'count' => Customer::where('group_id', $group->id)->where("balance", "!=", 0)->count() // need stats for this
                ],
            ],
            'Human Resources' => [
                [
                    'name' => 'Workplaces',
                    'icon' => 'fal fa-building',
                    'route' => 'grp.overview.human-resources.workplaces.index',
                    'count' => $group->humanResourcesStats->number_workplaces
                ],
                [
                    'name' => 'Responsibilities',
                    'icon' => 'fal fa-clipboard-list-check',
                    'route' => 'grp.overview.human-resources.responsibilities.index',
                    'count' => $group->humanResourcesStats->number_job_positions
                ],
                [
                    'name' => 'Employees',
                    'icon' => 'fal fa-user-hard-hat',
                    'route' => 'grp.overview.accounting.employees.index',
                    'count' => $group->humanResourcesStats->number_employees
                ],
                [
                    'name' => 'Clocking Machines',
                    'icon' => 'fal fa-chess-clock',
                    'route' => 'grp.overview.accounting.clocking-machines.index',
                    'count' => $group->humanResourcesStats->number_clocking_machines
                ],
                [
                    'name' => 'Timesheets',
                    'icon' => 'fal fa-stopwatch',
                    'route' => 'grp.overview.accounting.timesheets.index',
                    'count' => Timesheet::where('group_id', $group->id)->count() // need stats for this
                ],
            ],
        ];
        return $section;
    }
}
