<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\UI\GetCustomerShowcase;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\CustomerFulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\CRM\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCustomer extends InertiaAction
{
    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("crm.{$this->shop->slug}.edit");

        return $request->user()->hasPermissionTo("crm.{$this->shop->slug}.view");
    }

    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request)->withTab(CustomerFulfilmentTabsEnum::values());

        return $this->handle($customer);
    }

    public function htmlResponse(Customer $customer, ActionRequest $request): Response
    {
        return Inertia::render(
            'Fulfilment/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
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
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new stored items'),
                            'label'   => __('create stored items'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.create',
                                'parameters' => [$customer->slug]
                            ]
                        ],
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
                    : Inertia::lazy(fn () => StoredItemResource::collection(IndexStoredItems::run($customer))),

                CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),

            ]
        )->table(IndexStoredItems::make()->tableStructure($customer->storedItems))
            ->table(IndexFulfilmentOrders::make()->tableStructure($customer));
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Customer $customer, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('customers')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $customer->name,
                        ],

                    ],
                    'suffix'=> $suffix

                ],
            ];
        };

        return match ($routeName) {
            'grp.fulfilment.customers.show' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['customer'],
                    [
                        'index' => [
                            'name'       => 'grp.fulfilment.customers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.fulfilment.customers.show',
                            'parameters' => [$routeParameters['customer']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
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
            'grp.fulfilment.customers.show' ,
            'shops.customers.show'=> [
                'label'=> $customer->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
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
