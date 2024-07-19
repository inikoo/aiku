<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:42:37 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Actions\Accounting\InvoiceTransaction\UI\IndexInvoiceTransactions;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Enums\UI\Accounting\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoicesResource;
use App\Http\Resources\Accounting\InvoiceTransactionsResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowInvoice extends OrgAction
{
    private Organisation|Fulfilment|FulfilmentCustomer|Shop $parent;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {

        if($this->parent instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
        } elseif($this->parent instanceof Shop) {
            //todo think about it
            return false;
        } elseif($this->parent instanceof Fulfilment) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif($this->parent instanceof FulfilmentCustomer) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }
        return false;
    }

    public function inOrganisation(Organisation $organisation, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$organisation;
        $this->initialisation($organisation, $request)->withTab(InvoiceTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$shop;
        $this->initialisationFromShop($shop, $request)->withTab(InvoiceTabsEnum::values());
        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(InvoiceTabsEnum::values());
        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(InvoiceTabsEnum::values());

        return $this->handle($invoice);
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): Response
    {
        $this->validateAttributes();
        // dd($invoice);
        $billingAddress = AddressResource::make($invoice->billingAddress);
        $address = AddressResource::make($invoice->address);
        $fixedAddresses = AddressResource::collection($invoice->fixedAddresses);
        return Inertia::render(
            'Org/Accounting/Invoice',
            [
                'title'                                 => __('invoice'),
                'breadcrumbs'                           => $this->getBreadcrumbs(
                    $invoice,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($invoice, $request),
                    'next'     => $this->getNext($invoice, $request),
                ],
                'pageHead'    => [
                    'model'     => __('invoice'),
                    'title'     => $invoice->number,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-file-invoice-dollar'],
                        'title' => $invoice->number
                    ]
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => InvoiceTabsEnum::navigation()
                ],
                'address'   => $address,
                'billing_address' => $billingAddress,
                'fixed_addresses' => $fixedAddresses,
                'order_summary'     => [
                    [
                        [
                            'label'         => __('Services'),
                            // 'quantity'      => $palletDelivery->number_pallets ?? 0,
                            // 'price_base'    => __('Multiple'),
                            'price_total'   => $invoice->services_amount
                        ],
                        [
                            'label'         => __('Physical Goods'),
                            // 'quantity'      => $palletDelivery->stats->number_services ?? 0,
                            // 'price_base'    => __('Multiple'),
                            'price_total'   => $invoice->goods_amount
                        ],
                        [
                            'label'         => __('Rental'),
                            // 'quantity'      => $palletDelivery->stats->number_services ?? 0,
                            // 'price_base'    => __('Multiple'),
                            'price_total'   => $invoice->rental_amount
                        ],
                    ],
                    [
                        [
                            'label'         => __('Charges'),
                            // 'information'   => __('Shipping fee to your address using DHL service.'),
                            'price_total'   => $invoice->charges_amount
                        ],
                        [
                            'label'         => __('Shipping'),
                            // 'information'   => __('Tax is based on 10% of total order.'),
                            'price_total'   => $invoice->shipping_amount
                        ],
                        [
                            'label'         => __('Insurance'),
                            // 'information'   => __('Tax is based on 10% of total order.'),
                            'price_total'   => $invoice->insurance_amount
                        ],
                        [
                            'label'         => __('Tax'),
                            'information'   => __('Tax is based on 10% of total order.'),
                            'price_total'   => $invoice->tax_amount
                        ],
                        [
                            'label'         => __('Discount'),
                            'information'   => __('Discounts applied within vouchers or seasonal promos.'),
                            'price_total'   => $invoice->discounts_total
                        ],
                    ],
                    [
                        [
                            'label'         => __('Total'),
                            'price_total'   => $invoice->total_amount
                        ],
                    ],
                ],

                'invoice'   => [
                    'number'                    => $invoice->number,
                    'profit_amount'             => $invoice->profit_amount,
                    'margin_percentage'         => $invoice->margin_percentage,
                    'date'                      => $invoice->date,

                    'currency_code'             => $invoice->currency->code,
                    'items_net'                 => $invoice->items_net,
                    'net_amount'                => $invoice->net_amount,
                    'tax_percentage'            => $invoice->tax_percentage,
                    'payment_amount'            => $invoice->payment_amount,

                    // 'item_gross'                => $invoice->item_gross,
                    // 'discounts_total'           => $invoice->discounts_total,
                    // 'charges'                   => $invoice->charges,
                    // 'shipping'                  => $invoice->shipping,
                    // 'insurance'                 => $invoice->insurance,
                    // 'tax_amount'       => $invoice->tax_amount,
                    // 'total_amount'     => $invoice->total_amount,

                ],

                InvoiceTabsEnum::SHOWCASE->value => $this->tab == InvoiceTabsEnum::SHOWCASE->value ?
                    fn () => GetInvoiceShowcase::run($invoice)
                    : Inertia::lazy(fn () => GetInvoiceShowcase::run($invoice)),

                InvoiceTabsEnum::ITEMS->value => $this->tab == InvoiceTabsEnum::ITEMS->value ?
                    fn () => InvoiceTransactionsResource::collection(IndexInvoiceTransactions::run($invoice, InvoiceTabsEnum::ITEMS->value))
                    : Inertia::lazy(fn () => InvoiceTransactionsResource::collection(IndexInvoiceTransactions::run($invoice, InvoiceTabsEnum::ITEMS->value))),

                InvoiceTabsEnum::PAYMENTS->value => $this->tab == InvoiceTabsEnum::PAYMENTS->value ?
                    fn () => PaymentsResource::collection(IndexPayments::run($invoice))
                    : Inertia::lazy(fn () => PaymentsResource::collection(IndexPayments::run($invoice))),



            ]
        )->table(IndexPayments::make()->tableStructure($invoice, [], InvoiceTabsEnum::PAYMENTS->value))
            ->table(IndexInvoiceTransactions::make()->tableStructure($invoice, InvoiceTabsEnum::ITEMS->value));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    public function jsonResponse(Invoice $invoice): InvoicesResource
    {
        return new InvoicesResource($invoice);
    }


    public function getBreadcrumbs(Invoice $invoice, string $routeName, array $routeParameters): array
    {


        return array_merge(
            match ($routeName) {
                'grp.org.fulfilments.show.operations.invoices.show'         => ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                'grp.org.fulfilments.show.crm.customers.show.invoices.show' => ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                default                                                     => []

            },
            [
                'grp.shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $invoice->id,
                    'name'            => $invoice->number,
                    'index'           => [
                        'route'   => 'grp.org.shops.index',
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
            'grp.org.accounting.invoices.show'=> [
                'label'=> $invoice->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $invoice->organisation->slug,
                        'invoice'     => $invoice->slug
                    ]

                ]
            ],

            'grp.org.fulfilments.show.operations.invoices.show'=> [
                'label'=> $invoice->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $invoice->organisation->slug,
                        'fulfilment'  => $this->parent->slug,
                        'invoice'     => $invoice->slug
                    ]

                ]
            ],

            'grp.org.fulfilments.show.crm.customers.show.invoices.show'=> [
                'label'=> $invoice->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'       => $invoice->organisation->slug,
                        'fulfilment'         => $this->parent->slug,
                        'fulfilmentCustomer' => $this->parent->slug,
                        'invoice'            => $invoice->slug
                    ]

                ]
            ],
        };
    }
}
