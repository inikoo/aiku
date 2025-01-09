<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:42:37 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Billing;

use App\Actions\Accounting\InvoiceTransaction\UI\IndexInvoiceTransactions;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\RetinaAction;
use App\Enums\UI\Accounting\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Http\Resources\Accounting\InvoiceTransactionsResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowInvoice extends RetinaAction
{
    private Organisation|Fulfilment|FulfilmentCustomer|Shop $parent;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function inRetina(Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $request->user()->customer->fulfilmentCustomer;
        $this->initialisation($request)->withTab(InvoiceTabsEnum::values());

        return $this->handle($invoice);
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): Response
    {
        $payAmount   = $invoice->total_amount - $invoice->payment_amount;
        $roundedDiff = round($payAmount, 2);

        return Inertia::render(
            'Billing/RetinaInvoice',
            [
                'title'       => __('invoice'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($invoice, $request),
                    'next'     => $this->getNext($invoice, $request),
                ],
                'pageHead'    => [
                    'model' => __('invoice'),
                    'title' => $invoice->reference,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-file-invoice-dollar'],
                        'title' => $invoice->reference
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => InvoiceTabsEnum::navigation()
                ],

                'order_summary' => [
                    [
                        [
                            'label'       => __('Services'),
                            'price_total' => $invoice->services_amount
                        ],
                        [
                            'label'       => __('Physical Goods'),
                            'price_total' => $invoice->goods_amount
                        ],
                        [
                            'label'       => __('Rental'),
                            'price_total' => $invoice->rental_amount
                        ],
                    ],
                    [
                        [
                            'label'       => __('Charges'),
                            'price_total' => $invoice->charges_amount
                        ],
                        [
                            'label'       => __('Shipping'),
                            'price_total' => $invoice->shipping_amount
                        ],
                        [
                            'label'       => __('Insurance'),
                            'price_total' => $invoice->insurance_amount
                        ],
                        [
                            'label'            => __('Tax'),
                            // 'information_icon' => __('xxx.'),
                            'information'      => '(vat)',
                            'price_total'      => $invoice->tax_amount
                        ],

                    ],
                    [
                        [
                            'label'       => __('Total'),
                            'price_total' => $invoice->total_amount
                        ],
                    ],
                ],

                'exportPdfRoute' => [
                    'name'       => 'retina.billing.invoices.download',
                    'parameters' => [
                        'invoice' => $invoice->slug
                    ]
                ],
                'box_stats'      => [
                    'customer'    => [
                        'route'        => [
                            'name'       => 'retina.billing.invoices.show',
                            'parameters' => [
                                'invoice' => $invoice->slug,
                            ]
                        ],
                        'slug'         => $invoice->customer->slug,
                        'reference'    => $invoice->customer->reference,
                        'contact_name' => $invoice->customer->contact_name,
                        'company_name' => $invoice->customer->company_name,
                        'location'     => $invoice->customer->location,
                        'phone'        => $invoice->customer->phone,
                        // 'address'      => AddressResource::collection($invoice->customer->addresses),
                    ],
                    'information' => [
                        'recurring_bill' => [
                            'reference' => $invoice->reference
                        ],
                        'routes'         => [
                            'fetch_payment_accounts' => [
                                'name'       => 'grp.json.shop.payment-accounts',
                                'parameters' => [
                                    'shop' => $invoice->shop->slug
                                ]
                            ],
                            'submit_payment'         => [
                                'name'       => 'grp.models.invoice.payment.store',
                                'parameters' => [
                                    'invoice'  => $invoice->id,
                                    'customer' => $invoice->customer_id,
                                ]
                            ]

                        ],
                        'paid_amount'    => $invoice->payment_amount,
                        'pay_amount'     => $roundedDiff
                    ]
                ],

                'invoice' => InvoiceResource::make($invoice),


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


    public function jsonResponse(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        return [];
    }

    public function getPrevious(Invoice $invoice, ActionRequest $request): ?array
    {
        $previous = Invoice::where('reference', '<', $invoice->reference)
            ->where('invoices.shop_id', $invoice->shop_id)
            ->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Invoice $invoice, ActionRequest $request): ?array
    {
        $next = Invoice::where('reference', '>', $invoice->reference)
            ->where('invoices.shop_id', $invoice->shop_id)
            ->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Invoice $invoice, string $routeName): ?array
    {
        if (!$invoice) {
            return null;
        }

        return match ($routeName) {
            'retina.billing.invoices.show' => [
                'label' => $invoice->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $invoice->organisation->slug,
                        'invoice'      => $invoice->slug
                    ]

                ]
            ],
        };
    }
}
