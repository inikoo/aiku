<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\UI\IndexShops;
use App\Actions\UI\WithInertia;
use App\Enums\UI\CustomerTabsEnum;
use App\Enums\UI\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Invoice;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Invoice $invoice
 */
class ShowInvoice extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {
        //
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($invoice);
    }

    public function inShop(Shop $shop, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($invoice);
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Invoice',
            [
                'title'       => __('invoice'),
                'breadcrumbs' => $this->getBreadcrumbs($invoice),
                'navigation'                            => [
                    'previous' => $this->getPrevious($invoice, $request),
                    'next'     => $this->getNext($invoice, $request),
                ],
                'pageHead'    => [
                    'title' => $invoice->number,


                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => InvoiceTabsEnum::navigation()

                ],

                InvoiceTabsEnum::PAYMENTS->value => $this->tab == InvoiceTabsEnum::PAYMENTS->value ?
                    fn () => PaymentResource::collection(IndexPayments::run($this->invoice))
                    : Inertia::lazy(fn () => PaymentResource::collection(IndexPayments::run($this->invoice))),



            ]
        )->table(IndexPayments::make()->tableStructure());
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }


    public function getBreadcrumbs(Invoice $invoice): array
    {
        //TODO Pending
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $invoice->id,
                    'name'            => $invoice->number,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Invoices list')
                    ],
                    'modelLabel' => [
                        'label' => __('invoice')
                    ],
                ],
            ]
        );
    }

    public function getPrevious(Invoice $invoice, ActionRequest $request): ?array
    {
        $previous = Invoice::where('number', '<', $invoice->number)->orderBy('number', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Invoice $invoice, ActionRequest $request): ?array
    {
        $next = Invoice::where('number', '>', $invoice->number)->orderBy('number')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Invoice $invoice, string $routeName): ?array
    {
        if(!$invoice) {
            return null;
        }
        return match ($routeName) {
            'accounting.invoices.show'=> [
                'label'=> $invoice->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'invoice'=> $invoice->number
                    ]

                ]
            ]
        };
    }
}
