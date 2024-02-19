<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\UI\GetCustomerShowcase;
use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\OrgAction;
use App\Actions\Traits\WithWebUserMeta;
use App\Enums\UI\CustomerFulfilmentTabsEnum;
use App\Http\Resources\CRM\WebUsersResource;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCustomer extends OrgAction
{
    use WithWebUserMeta;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomer
    {
        return $fulfilmentCustomer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): FulfilmentCustomer
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(CustomerFulfilmentTabsEnum::values());

        return $this->handle($fulfilmentCustomer);
    }

    public function htmlResponse(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {

        $webUsersMeta = $this->getWebUserMeta($fulfilmentCustomer->customer, $request);


        return Inertia::render(
            'Org/Fulfilment/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($fulfilmentCustomer, $request),
                    'next'     => $this->getNext($fulfilmentCustomer, $request),
                ],
                'pageHead'    => [
                    'icon'    => [
                        'title' => __('customer'),
                        'icon'  => 'fal fa-user'
                    ],
                    'meta'    => array_filter([
                        $webUsersMeta
                    ]),
                    'title'   => $fulfilmentCustomer->customer->name,
                    'edit'    => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new delivery'),
                            'label'   => __('new delivery'),
                            'options' => [
                                'warehouses' => WarehouseResource::collection($fulfilmentCustomer->fulfilment->warehouses)
                            ],
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.pallet-delivery.store',
                                'parameters' => [$fulfilmentCustomer->id]
                            ]
                        ],
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => CustomerFulfilmentTabsEnum::navigation()
                ],

                CustomerFulfilmentTabsEnum::SHOWCASE->value => $this->tab == CustomerFulfilmentTabsEnum::SHOWCASE->value ?
                    fn () => GetCustomerShowcase::run($fulfilmentCustomer->customer)
                    : Inertia::lazy(fn () => GetCustomerShowcase::run($fulfilmentCustomer->customer)),


                CustomerFulfilmentTabsEnum::PALLETS->value => $this->tab == CustomerFulfilmentTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($fulfilmentCustomer))),

                CustomerFulfilmentTabsEnum::STORED_ITEMS->value => $this->tab == CustomerFulfilmentTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItems::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexStoredItems::run($fulfilmentCustomer))),

                CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value => $this->tab == CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value ?
                    fn () => PalletDeliveriesResource::collection(IndexPalletDeliveries::run($fulfilmentCustomer->fulfilment))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexPalletDeliveries::run($fulfilmentCustomer->fulfilment))),

                CustomerFulfilmentTabsEnum::PALLET_RETURNS->value => $this->tab == CustomerFulfilmentTabsEnum::PALLET_RETURNS->value ?
                    fn () => PalletReturnsResource::collection(IndexPalletReturns::run($fulfilmentCustomer->fulfilment, CustomerFulfilmentTabsEnum::PALLET_RETURNS->value))
                    : Inertia::lazy(fn () => PalletReturnsResource::collection(IndexPalletReturns::run($fulfilmentCustomer->fulfilment, CustomerFulfilmentTabsEnum::PALLET_RETURNS->value))),

                CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($fulfilmentCustomer))),

                CustomerFulfilmentTabsEnum::WEB_USERS->value => $this->tab == CustomerFulfilmentTabsEnum::WEB_USERS->value ?
                    fn () => WebUsersResource::collection(IndexWebUsers::run($fulfilmentCustomer->customer))
                    : Inertia::lazy(fn () => WebUsersResource::collection(IndexWebUsers::run($fulfilmentCustomer->customer))),


            ]
        )->table(IndexStoredItems::make()->tableStructure($fulfilmentCustomer->storedItems))
            ->table(
                IndexPalletDeliveries::make()->tableStructure(
                    $fulfilmentCustomer,
                    prefix: CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value
                )
            )
            ->table(
                IndexPalletReturns::make()->tableStructure(
                    $fulfilmentCustomer,
                    prefix: CustomerFulfilmentTabsEnum::PALLET_RETURNS->value
                )
            )->table(
                IndexPallets::make()->tableStructure(
                    parent: $fulfilmentCustomer,
                    prefix: CustomerFulfilmentTabsEnum::PALLETS->value
                )
            );
    }


    public function jsonResponse(Customer $fulfilmentCustomer): CustomerResource
    {
        return new CustomerResource($fulfilmentCustomer);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (FulfilmentCustomer $fulfilmentCustomer, array $routeParameters, string $suffix = '') {
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
                            'label' => $fulfilmentCustomer->customer->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        $fulfilmentCustomer = FulfilmentCustomer::where('slug', $routeParameters['fulfilmentCustomer'])->first();

        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(
                Arr::only($routeParameters, ['organisation', 'fulfilment'])
            ),
            $headCrumb(
                $fulfilmentCustomer,
                [

                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                        'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                    ],
                    'model' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                        'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                    ]
                ]
            )
        );
    }

    public function getPrevious(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $previous = FulfilmentCustomer::where('slug', '<', $fulfilmentCustomer->slug)
            ->where('fulfilment_customers.fulfilment_id', $fulfilmentCustomer->fulfilment_id)
            ->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $next = FulfilmentCustomer::where('slug', '>', $fulfilmentCustomer->slug)
            ->where('fulfilment_customers.fulfilment_id', $fulfilmentCustomer->fulfilment_id)
            ->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?FulfilmentCustomer $fulfilmentCustomer, string $routeName): ?array
    {
        if (!$fulfilmentCustomer) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show' => [
                'label' => $fulfilmentCustomer->customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $fulfilmentCustomer->organisation->slug,
                        'fulfilment'         => $this->fulfilment->slug,
                        'fulfilmentCustomer' => $fulfilmentCustomer->slug
                    ]

                ]
            ],
        };
    }
}
