<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Invoice\UI\ShowInvoice;
use App\Actions\Accounting\InvoiceTransaction\UI\IndexRefundInProcessTransactions;
use App\Actions\Accounting\InvoiceTransaction\UI\IndexRefundTransactions;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Enums\UI\Accounting\InvoiceRefundTabsEnum;
use App\Http\Resources\Accounting\InvoiceRefundResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Http\Resources\Accounting\RefundInProcessTransactionsResource;
use App\Http\Resources\Accounting\RefundTransactionsResource;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRefund extends OrgAction
{
    use WithFulfilmentCustomerSubNavigation;
    private Invoice|Organisation|Fulfilment|FulfilmentCustomer|Shop $parent;

    public function handle(Invoice $refund): Invoice
    {
        return $refund;
    }

    public function authorize(ActionRequest $request): bool
    {
        $routeName = $request->route()->getName();

        // dd($routeName);
        if ($routeName == 'grp.org.accounting.invoices.show.refunds.show') {
            return $request->user()->authTo("accounting.{$this->organisation->id}.view");
        } elseif ($routeName == 'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.show') {
            return $request->user()->authTo(
                [
                    "fulfilment-shop.{$this->fulfilment->id}.view",
                    "accounting.{$this->fulfilment->organisation_id}.view"
                ]
            );
        }


        //        if ($this->parent instanceof Organisation) {
        //            return $request->user()->authTo("accounting.{$this->organisation->id}.view");
        //        } elseif ($this->parent instanceof Shop) {
        //            //todo think about it
        //            return false;
        //        } elseif ($this->parent instanceof Fulfilment) {
        //            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        //        } elseif ($this->parent instanceof FulfilmentCustomer) {
        //            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        //        }elseif ($this->parent instanceof Invoice) {
        //            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        //        }

        return false;
    }


    public function inInvoiceInOrganisation(Organisation $organisation, Invoice $invoice, Invoice $refund, ActionRequest $request): Invoice
    {
        $this->parent = $invoice;
        $this->initialisation($organisation, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($refund);
    }

    public function inOrganisation(Organisation $organisation, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Invoice $refund, ActionRequest $request): Invoice
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($refund);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inInvoiceInFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Invoice $invoice, Invoice $refund, ActionRequest $request): Invoice
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(InvoiceRefundTabsEnum::values());

        return $this->handle($refund);
    }


    public function htmlResponse(Invoice $refund, ActionRequest $request): Response
    {
        $subNavigation = [];
        if ($this->parent instanceof FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
        }

        if ($refund->recurringBill()->exists()) {
            if ($this->parent instanceof Fulfilment) {
                $recurringBillRoute = [
                    'name'       => 'grp.org.fulfilments.show.operations.recurring_bills.show',
                    'parameters' => [$refund->organisation->slug, $this->parent->slug, $refund->recurringBill->slug]
                ];
            } else {
                $recurringBillRoute = [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                    'parameters' => [$refund->organisation->slug, $this->parent->fulfilment->slug, $this->parent->slug, $refund->recurringBill->slug]
                ];
            }
        } else {
            $recurringBillRoute = null;
        }
        $payAmount   = $refund->total_amount - $refund->payment_amount;
        $roundedDiff = round($payAmount, 2);

        $customerRoute = [];

        if ($this->parent instanceof Fulfilment) {
            $customerRoute = [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show',
                'parameters' => [
                    'organisation'       => $refund->organisation->slug,
                    'fulfilment'         => $refund->customer->fulfilmentCustomer->fulfilment->slug,
                    'fulfilmentCustomer' => $refund->customer->fulfilmentCustomer->slug,
                ]
            ];
        } else {
            $customerRoute = [
                'name'       => 'grp.org.shops.show.crm.customers.show',
                'parameters' => [
                    'organisation' => $refund->organisation->slug,
                    'shop'         => $refund->shop->slug,
                    'customer'     => $refund->customer->slug,
                ]
            ];
        }

        $actions = [];

        if ($refund->in_process && (!app()->environment('production'))) {
            $actions[] = [
                'type'  => 'button',
                'style' => 'delete',
                'label' => __('Delete'),
                'key'   => 'delete_refund',
                'route' => [
                    'method'     => 'delete',
                    'name'       => 'grp.models.refund.delete',
                    'parameters' => [
                        'invoice' => $refund->id,
                    ]
                ]
            ];
            $actions[] = [
                'type'  => 'button',
                'style' => 'create',
                'label' => __('Finalise refund'),
                'key'   => 'finalise_refund',
                'route' => [
                    'method'     => 'post',
                    'name'       => '',
                    'parameters' => [
                        'invoice' => $refund->id,
                    ]
                ]
            ];
        }



        return Inertia::render(
            'Org/Accounting/InvoiceRefund',
            [
                'title'       => __('invoice refund'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $refund,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($refund, $request),
                    'next'     => $this->getNext($refund, $request),
                ],
                'pageHead'    => [
                    'subNavigation' => $subNavigation,
                    'model'   => __('invoice refund'),
                    'title'   => $refund->reference,
                    'icon'    => [
                        'icon'  => ['fas', 'fa-hand-holding-usd'],
                        'title' => $refund->reference
                    ],
                    'actions' => $actions
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => InvoiceRefundTabsEnum::navigation()
                ],

                'order_summary' => [
                    [
                        [
                            'label'       => __('Services'),
                            'price_total' => $refund->services_amount
                        ],
                        [
                            'label'       => __('Physical Goods'),
                            'price_total' => $refund->goods_amount
                        ],
                        [
                            'label'       => __('Rental'),
                            'price_total' => $refund->rental_amount
                        ],
                    ],
                    [
                        [
                            'label'       => __('Tax'),
                            'price_total' => $refund->tax_amount
                        ]
                    ],
                    [
                        [
                            'label'       => __('Total'),
                            'price_total' => $refund->total_amount
                        ],
                    ],
                ],

                'exportPdfRoute' => [
                    'name'       => 'grp.org.accounting.invoices.download',
                    'parameters' => [
                        'organisation' => $refund->organisation->slug,
                        'invoice'      => $refund->slug
                    ]
                ],
                'box_stats'      => [
                    'customer'    => [
                        'slug'         => $refund->customer->slug,
                        'reference'    => $refund->customer->reference,
                        'route'        => $customerRoute,
                        'contact_name' => $refund->customer->contact_name,
                        'company_name' => $refund->customer->company_name,
                        'location'     => $refund->customer->location,
                        'phone'        => $refund->customer->phone,
                        // 'address'      => AddressResource::collection($invoice->customer->addresses),
                    ],
                    'information' => [
                        'recurring_bill' => [
                            'reference' => $refund->reference,
                            'route'     => $recurringBillRoute
                        ],
                        'routes'         => [
                            'fetch_payment_accounts' => [
                                'name'       => 'grp.json.shop.payment-accounts',
                                'parameters' => [
                                    'shop' => $refund->shop->slug
                                ]
                            ],
                            'submit_payment'         => [
                                'name'       => 'grp.models.invoice.payment.store',
                                'parameters' => [
                                    'invoice'  => $refund->id,
                                    'customer' => $refund->customer_id,
                                ]
                            ]

                        ],
                        'paid_amount'    => $refund->payment_amount,
                        'pay_amount'     => $roundedDiff
                    ]
                ],

                'invoice_refund' => InvoiceRefundResource::make($refund),


                InvoiceRefundTabsEnum::ITEMS->value => $this->tab == InvoiceRefundTabsEnum::ITEMS->value ?
                    fn () => RefundTransactionsResource::collection(IndexRefundTransactions::run($refund->originalInvoice, InvoiceRefundTabsEnum::ITEMS->value))
                    : Inertia::lazy(fn () => RefundTransactionsResource::collection(IndexRefundTransactions::run($refund->originalInvoice, InvoiceRefundTabsEnum::ITEMS->value))),

                InvoiceRefundTabsEnum::ITEMS_IN_PROCESS->value => $this->tab == InvoiceRefundTabsEnum::ITEMS_IN_PROCESS->value ?
                    fn () => RefundInProcessTransactionsResource::collection(IndexRefundInProcessTransactions::run($refund->originalInvoice, InvoiceRefundTabsEnum::ITEMS_IN_PROCESS->value))
                    : Inertia::lazy(fn () => RefundInProcessTransactionsResource::collection(IndexRefundInProcessTransactions::run($refund->originalInvoice, InvoiceRefundTabsEnum::ITEMS_IN_PROCESS->value))),

                InvoiceRefundTabsEnum::PAYMENTS->value => $this->tab == InvoiceRefundTabsEnum::PAYMENTS->value ?
                    fn () => PaymentsResource::collection(IndexPayments::run($refund))
                    : Inertia::lazy(fn () => PaymentsResource::collection(IndexPayments::run($refund))),


            ]
        )->table(IndexPayments::make()->tableStructure($refund, [], InvoiceRefundTabsEnum::PAYMENTS->value))
            ->table(IndexRefundTransactions::make()->tableStructure($refund, InvoiceRefundTabsEnum::ITEMS->value))
            ->table(IndexRefundInProcessTransactions::make()->tableStructure($refund, InvoiceRefundTabsEnum::ITEMS_IN_PROCESS->value));
    }


    public function jsonResponse(Invoice $invoice): InvoiceRefundResource
    {
        return new InvoiceRefundResource($invoice);
    }


    public function getBreadcrumbs(Invoice $refund, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Invoice $refund, array $routeParameters, string $suffix = null, $suffixIndex = '') {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Refunds').$suffixIndex,
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $refund->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };


        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.show',
            => array_merge(
                ShowInvoice::make()->getBreadcrumbs('grp.org.fulfilments.show.crm.customers.show.invoices.show', Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'invoice'])),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'invoice'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'invoice', 'refund'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.fulfilments.show.operations.invoices.show',
            => array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'invoice'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.fulfilments.show.operations.invoices.all_invoices.show',
            => array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.invoices.all_invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'invoice'])
                        ]
                    ],
                    $suffix,
                    ' ('.__('All').')'
                ),
            ),
            'grp.org.fulfilments.show.operations.invoices.unpaid_invoices.show',
            => array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.unpaid_invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.invoices.unpaid_invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'invoice'])
                        ]
                    ],
                    $suffix,
                    ' ('.__('Unpaid').')'
                ),
            ),

            'grp.org.fulfilments.show.crm.customers.show.invoices.show',
            => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'invoice'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.fulfilments.show.crm.customers.show.invoices.refund.show',
            => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.refund.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'invoice'])
                        ]
                    ],
                    $suffix
                ),
            ),

            'grp.org.accounting.invoices.all_invoices.show',
            => array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.invoices.all_invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'invoice'])
                        ]
                    ],
                    $suffix,
                    ' ('.__('All').')'
                ),
            ),

            'grp.org.accounting.invoices.unpaid_invoices.show',
            => array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.invoices.unpaid_invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.invoices.unpaid_invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'invoice'])
                        ]
                    ],
                    $suffix,
                    ' ('.__('Unpaid').')'
                ),
            ),

            'grp.org.accounting.invoices.show',
            => array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb(
                    $refund,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.invoices.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'invoice'])
                        ]
                    ],
                    $suffix
                ),
            ),


            default => []
        };
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

    private function getNavigation(?Invoice $refund, string $routeName): ?array
    {
        if (!$refund) {
            return null;
        }


        return match ($routeName) {
            'grp.org.accounting.invoices.show' => [
                'label' => $refund->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $refund->organisation->slug,
                        'invoice'      => $refund->slug
                    ]

                ]
            ],


            'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.show' => [
                'label' => $refund->reference,
                'route' => [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.show.refunds.show',
                    'parameters' => [
                        'organisation'       => $refund->organisation->slug,
                        'fulfilment'         => $refund->shop->fulfilment->slug,
                        'fulfilmentCustomer' => $this->parent->customer->fulfilmentCustomer->slug,
                        'invoice'            => $this->parent->slug,
                        'refund'             => $refund->slug
                    ]
                ]
            ],
        };
    }
}
