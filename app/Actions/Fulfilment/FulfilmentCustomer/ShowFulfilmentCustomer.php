<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\UI\GetCustomerShowcase;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\Fulfilment\ShowFulfilmentsDashboard;
use App\Enums\UI\CustomerFulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCustomer extends OrgAction
{
    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($customer);
    }

    public function htmlResponse(Customer $customer, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($customer, $request),
                    'next'     => $this->getNext($customer, $request),
                ],
                'pageHead'    => [
                    'title' => $customer->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions'=> [
                       /* [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new delivery'),
                            'label'   => __('create delivery'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.pallets.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],*/
                        /*[
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('upload stored items'),
                            'label'   => __('upload stored items'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.create', // TODO Create Action for upload CSV/XLSX
                                'parameters' => [$customer->slug]
                            ]
                        ],*/
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => CustomerFulfilmentTabsEnum::navigation()
                ],

                CustomerFulfilmentTabsEnum::SHOWCASE->value => $this->tab == CustomerFulfilmentTabsEnum::SHOWCASE->value ?
                    fn () => GetCustomerShowcase::run($customer)
                    : Inertia::lazy(fn () => GetCustomerShowcase::run($customer)),

                CustomerFulfilmentTabsEnum::ORDERS->value => $this->tab == CustomerFulfilmentTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexFulfilmentOrders::run($customer))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexFulfilmentOrders::run($customer))),

                CustomerFulfilmentTabsEnum::STORED_ITEMS->value => $this->tab == CustomerFulfilmentTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItems::run($customer))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexStoredItems::run($customer))),

                CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value => $this->tab == CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value ?
                    fn () => StoredItemResource::collection(IndexPalletDeliveries::run($customer->fulfilmentCustomer->fulfilment))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexPalletDeliveries::run($customer->fulfilmentCustomer->fulfilment))),

                CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),

            ]
        )->table(IndexStoredItems::make()->tableStructure($customer->storedItems))
            ->table(IndexPalletDeliveries::make()->tableStructure($customer->fulfilmentCustomer->fulfilment, prefix: CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value))
            ->table(IndexFulfilmentOrders::make()->tableStructure($customer));
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => __($routeParameters['parameters']['customer'])
                    ],
                ],
            ];
        };


        return array_merge(
            IndexFulfilmentCustomers::make()->getBreadcrumbs(
                $routeParameters
            ),
            $headCrumb(
                [
                    'name'       => 'grp.org.fulfilments.show.customers.index',
                    'parameters' => [
                        'organisation' => $routeParameters['organisation']->slug,
                        'fulfilment' => $routeParameters['fulfilment']->slug,
                        'customer' => $routeParameters['customer']->slug
                    ]
                ]
            )
        );
    }

    public function getPrevious(Customer $customer, ActionRequest $request): ?array
    {

        $previous = Customer::where('slug', '<', $customer->slug)->when(true, function ($query) use ($customer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $customer->shop_id);
            }
        })->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Customer $customer, ActionRequest $request): ?array
    {
        $next = Customer::where('slug', '>', $customer->slug)->when(true, function ($query) use ($customer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $customer->shop_id);
            }
        })->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Customer $customer, string $routeName): ?array
    {
        if(!$customer) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.customers.show' ,
            'shops.customers.show'=> [
                'label'=> $customer->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $customer->shop->organisation->slug,
                        'fulfilment'=> $this->fulfilment->slug,
                        'customer'=> $customer->slug
                    ]

                ]
            ],
            'shops.show.customers.show'=> [
                'label'=> $customer->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'    => $customer->shop->slug,
                        'customer'=> $customer->slug
                    ]

                ]
            ]
        };
    }
}
