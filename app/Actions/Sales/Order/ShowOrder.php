<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\Sales\Invoice\IndexInvoices;
use App\Actions\UI\WithInertia;
use App\Enums\UI\OrderTabsEnum;
use App\Http\Resources\Accounting\PaymentResource;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Http\Resources\Sales\InvoiceResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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

    public function asController(Order $order): Order
    {
        return $this->handle($order);
    }

    public function inShop(Shop $shop, Order $order, Request $request): Order
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($order);
    }

    public function htmlResponse(Order $order): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Order',
            [
                'title'       => __('order'),
                'breadcrumbs' => $this->getBreadcrumbs($order),
                'pageHead'    => [
                    'title' => $order->number,


                ],
                'tabs' => [
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

    #[Pure] public function jsonResponse(Order $order): OrderResource
    {
        return new OrderResource($order);
    }


    public function getBreadcrumbs(Order $order): array
    {
        //TODO Pending
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $order->id,
                    'name'            => $order->number,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Orders list')
                    ],
                    'modelLabel' => [
                        'label' => __('order')
                    ],
                ],
            ]
        );
    }
}
