<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Customer\UI\ShowCustomer;
use App\Actions\UI\WithInertia;
use App\Enums\UI\OrderTabsEnum;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Order;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Order $order
 */
class ShowOrder extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Order $order): Order
    {
        return $order;
    }

    public function authorize(ActionRequest $request): bool
    {
        //TODO Change permission
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Order $order, ActionRequest $request): Order
    {
        $this->initialisation($request)->withTab(OrderTabsEnum::values());

        return $this->handle($order);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Order $order, ActionRequest $request): Order
    {
        $this->initialisation($request)->withTab(OrderTabsEnum::values());

        return $this->handle($order);
    }

    public function htmlResponse(Order $order, ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Order',
            [
                'title'       => __('order'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters(),
                ),
                'pageHead'    => [
                    'title' => $order->number,


                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => OrderTabsEnum::navigation()

                ],

                OrderTabsEnum::PAYMENTS->value => $this->tab == OrderTabsEnum::PAYMENTS->value ?
                    fn () => PaymentResource::collection(IndexPayments::run($this->order))
                    : Inertia::lazy(fn () => PaymentResource::collection(IndexPayments::run($this->order))),

                OrderTabsEnum::INVOICES->value => $this->tab == OrderTabsEnum::INVOICES->value ?
                    fn () => InvoiceResource::collection(IndexInvoices::run($this->order))
                    : Inertia::lazy(fn () => InvoiceResource::collection(IndexInvoices::run($this->order))),

                OrderTabsEnum::DELIVERY_NOTES->value => $this->tab == OrderTabsEnum::DELIVERY_NOTES->value ?
                    fn () => DeliveryNoteResource::collection(IndexDeliveryNotes::run($this->order))
                    : Inertia::lazy(fn () => DeliveryNoteResource::collection(IndexDeliveryNotes::run($this->order))),

            ]
        )->table(IndexPayments::make()->tableStructure())
            ->table(IndexInvoices::make()->tableStructure($order))
            ->table(IndexDeliveryNotes::make()->tableStructure($order));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return new OrderResource($order);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $order = $routeParameters['order'];


        $headCrumb = function (array $parameters = []) use ($order, $routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $parameters,
                    'name'            => $order->number,
                    'index'           =>
                        match ($routeName) {
                            'shops.show.customers.show.orders.show', 'customers.show.orders.show' => null,

                            default => [
                                'route'           => preg_replace('/(show|edit)$/', 'index', $routeName),
                                'routeParameters' => function () use ($parameters) {
                                    $indexParameters = $parameters;
                                    array_pop($indexParameters);

                                    return $indexParameters;
                                },
                                'overlay'         => __('order list')
                            ],
                        },


                    'modelLabel' => [
                        'label' => __('order')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'shops.show.customers.show.orders.show' => array_merge(
                ShowCustomer::make()->getBreadcrumbs('shops.show.customers.show', $routeParameters['customer']),
                $headCrumb([$routeParameters['shop']->slug, $routeParameters['customer']->slug, $routeParameters['order']->slug])
            ),
            'customers.show.orders.show' => array_merge(
                ShowCustomer::make()->getBreadcrumbs('customers.show', $routeParameters['customer']),
                $headCrumb([$routeParameters['customer']->slug, $routeParameters['order']->slug])
            ),
            'shops.show.orders.show' => array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters['shop']),
                $headCrumb([$routeParameters['shop']->slug, $routeParameters['order']->slug])
            ),
            'orders.show' => $headCrumb([$routeParameters['order']->slug])
        };
    }
}
