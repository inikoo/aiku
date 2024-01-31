<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\UI\GetCustomerShowcase;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\OrgAction;
use App\Enums\UI\CustomerFulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCustomer extends OrgAction
{
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
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function htmlResponse(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
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
                    'title'   => $fulfilmentCustomer->customer->name,
                    'edit'    => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions'=> [
                        [
                             'type'    => 'button',
                             'style'   => 'create',
                             'tooltip' => __('new delivery'),
                             'label'   => __('create delivery'),
                             'route'   => [
                                 'method'     => 'post',
                                 'name'       => 'grp.models.org.fulfilment.delivery.pallet.store',
                                 'parameters' => array_values($request->route()->originalParameters())
                             ]
                         ],
                     ]
                 ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => CustomerFulfilmentTabsEnum::navigation()
                ],

                CustomerFulfilmentTabsEnum::SHOWCASE->value => $this->tab == CustomerFulfilmentTabsEnum::SHOWCASE->value ?
                    fn () => GetCustomerShowcase::run($fulfilmentCustomer)
                    : Inertia::lazy(fn () => GetCustomerShowcase::run($fulfilmentCustomer)),

                CustomerFulfilmentTabsEnum::ORDERS->value => $this->tab == CustomerFulfilmentTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexFulfilmentOrders::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexFulfilmentOrders::run($fulfilmentCustomer))),

                CustomerFulfilmentTabsEnum::STORED_ITEMS->value => $this->tab == CustomerFulfilmentTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItems::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexStoredItems::run($fulfilmentCustomer))),

                CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value => $this->tab == CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value ?
                    fn () => StoredItemResource::collection(IndexPalletDeliveries::run($fulfilmentCustomer->fulfilment))
                    : Inertia::lazy(fn () => PalletDeliveriesResource::collection(IndexPalletDeliveries::run($fulfilmentCustomer->fulfilment))),

                CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($fulfilmentCustomer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($fulfilmentCustomer))),

            ]
        )->table(IndexStoredItems::make()->tableStructure($fulfilmentCustomer->storedItems))
            ->table(
                IndexPalletDeliveries::make()->tableStructure(
                    $fulfilmentCustomer->fulfilment,
                    prefix: CustomerFulfilmentTabsEnum::PALLET_DELIVERIES->value
                )
            )
            ->table(IndexFulfilmentOrders::make()->tableStructure($fulfilmentCustomer));
    }


    public function jsonResponse(Customer $fulfilmentCustomer): CustomerResource
    {
        return new CustomerResource($fulfilmentCustomer);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => __($routeParameters['parameters']['fulfilmentCustomer'])
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
                    'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                    'parameters' => $routeParameters
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
                        'organisation'           => $fulfilmentCustomer->organisation->slug,
                        'fulfilment'             => $this->fulfilment->slug,
                        'fulfilmentCustomer'     => $fulfilmentCustomer->slug
                    ]

                ]
            ],
        };
    }
}
