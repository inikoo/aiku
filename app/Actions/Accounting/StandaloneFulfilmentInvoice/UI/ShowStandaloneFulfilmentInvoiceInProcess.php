<?php
/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-09h-49m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoice\UI;

use App\Actions\Accounting\Invoice\UI\IsInvoiceUI;
use App\Actions\Accounting\InvoiceTransaction\UI\IndexInvoiceTransactions;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction\UI\IndexStandaloneFulfilmentInvoiceTransactions;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\UI\Accounting\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Http\Resources\Accounting\InvoiceTransactionsResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Http\Resources\Accounting\StandaloneFulfilmentInvoiceTransactionsResource;
use App\Models\Accounting\Invoice;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowStandaloneFulfilmentInvoiceInProcess extends OrgAction
{
    use IsInvoiceUI;
    use WithFulfilmentCustomerSubNavigation;

    private Organisation|Fulfilment|FulfilmentCustomer|Shop $parent;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }


    public function asController(Organisation $organisation, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(InvoiceTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(InvoiceTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent = $fulfilment;
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
        $subNavigation = [];
        if ($this->parent instanceof FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);

            $serviceRoute = [
                'name'       => 'grp.json.fulfilment.invoice.services.index',
                'parameters' => [
                    'fulfilment' => $invoice->shop->fulfilment->slug,
                    'scope'      => $invoice->slug
                ]
            ];

            $productRoute = [
                'name'       => 'grp.json.fulfilment.invoice.physical-goods.index',
                'parameters' => [
                    'fulfilment' => $invoice->shop->fulfilment->slug,
                    'scope'      => $invoice->slug
                ]
                ];
        }

        $actions = [];
        if (!app()->environment('production')) {
            $actions[] = [
                'type'    => 'button',
                'style'   => 'secondary',
                'icon'    => 'fal fa-plus',
                'key'     => 'add-service',
                'label'   => __('add service'),
                'tooltip' => __('Add single service'),
                'route'   => [
                    'name'       => 'grp.models.standalone-invoice.transaction.store',
                    'parameters' => [
                        'invoice' => $invoice->id
                    ]
                ]
            ];
            $actions[]  =   [
                'type'    => 'button',
                'style'   => 'secondary',
                'icon'    => 'fal fa-plus',
                'key'     => 'add-physical-good',
                'label'   => __('add physical good'),
                'tooltip' => __('Add physical good'),
                'route'   => [
                    'name'       => 'grp.models.standalone-invoice.transaction.store',
                    'parameters' => [
                        'invoice' => $invoice->id
                    ]
                ]
            ];
            $actions[] =
                [
                    'type'  => 'button',
                    // 'style' => 'tertiary',
                    'label' => __('complete invoice'),
                    'key'   => 'send-invoice',
                    'route' => [
                        'method'     => 'post',
                        'name'       => 'grp.models.standalone-invoice.complete',
                        'parameters' => [
                            'invoice' => $invoice->id
                        ]
                    ]
                ];
        }

        return Inertia::render(
            'Org/Accounting/InvoiceManual',
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
                    'subNavigation' => $subNavigation,
                    'model'         => __('invoice'),
                    'title'         => $invoice->reference,
                    'icon'          => [
                        'icon'  => ['fal', 'fa-file-invoice-dollar'],
                        'title' => $invoice->reference
                    ],
                    'actions'       => $actions
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
                            // 'information'   => __('Shipping fee to your address using DHL service.'),
                            'price_total' => $invoice->charges_amount
                        ],
                        [
                            'label'       => __('Shipping'),
                            // 'information'   => __('Tax is based on 10% of total order.'),
                            'price_total' => $invoice->shipping_amount
                        ],
                        [
                            'label'       => __('Insurance'),
                            // 'information'   => __('Tax is based on 10% of total order.'),
                            'price_total' => $invoice->insurance_amount
                        ],
                        [
                            'label'       => __('Tax'),
                            'price_total' => $invoice->tax_amount
                        ]
                    ],
                    [
                        [
                            'label'       => __('Total'),
                            'price_total' => $invoice->total_amount
                        ],
                    ],
                ],

                'exportPdfRoute' => [
                    'name'       => 'grp.org.accounting.invoices.download',
                    'parameters' => [
                        'organisation' => $invoice->organisation->slug,
                        'invoice'      => $invoice->slug
                    ]
                ],
                'box_stats'      => $this->getBoxStats($invoice),
                
                'service_list_route'       => $serviceRoute,
                'physical_good_list_route' => $productRoute,

                'invoice' => InvoiceResource::make($invoice),
                'outbox'  => [
                    'state'          => $invoice->shop->outboxes()->where('code', OutboxCodeEnum::SEND_INVOICE_TO_CUSTOMER->value)->first()?->state->value,
                    'workshop_route' => $this->getOutboxRoute($invoice)
                ],

                InvoiceTabsEnum::ITEMS->value => $this->tab == InvoiceTabsEnum::ITEMS->value ?
                    fn () => StandaloneFulfilmentInvoiceTransactionsResource::collection(IndexStandaloneFulfilmentInvoiceTransactions::run($invoice, InvoiceTabsEnum::ITEMS->value))
                    : Inertia::lazy(fn () => StandaloneFulfilmentInvoiceTransactionsResource::collection(IndexStandaloneFulfilmentInvoiceTransactions::run($invoice, InvoiceTabsEnum::ITEMS->value))),

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
        $headCrumb = function (Invoice $invoice, array $routeParameters, string $suffix = null, $suffixIndex = '') {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Invoices').$suffixIndex,
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $invoice->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };
        $invoice   = Invoice::where('slug', $routeParameters['invoice'])->first();

        return match ($routeName) {
            'grp.org.fulfilments.show.operations.invoices.show',
            => array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $invoice,
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
                    $invoice,
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
                    $invoice,
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

            'grp.org.fulfilments.show.crm.customers.show.invoices.in-process.show',
            => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $invoice,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.invoices.in-process.show',
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
                    $invoice,
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
                    $invoice,
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
                    $invoice,
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
        $previous = Invoice::onlyTrashed()->where('reference', '<', $invoice->reference)
            ->where('invoices.shop_id', $invoice->shop_id)
            ->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Invoice $invoice, ActionRequest $request): ?array
    {
        $next = Invoice::onlyTrashed()->where('reference', '>', $invoice->reference)
            ->where('invoices.shop_id', $invoice->shop_id)
            ->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Invoice $invoice, string $routeName): ?array
    {
        if (!$invoice) {
            return null;
        }

        // $isInvoice = $invoice->type === InvoiceTypeEnum::INVOICE;

        return match ($routeName) {
            'grp.org.accounting.invoices.show', 'grp.org.accounting.invoices.all_invoices.show', 'grp.org.accounting.invoices.unpaid_invoices.show' => [
                'label' => $invoice->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $invoice->organisation->slug,
                        'invoice'      => $invoice->slug
                    ]

                ]
            ],
            'grp.org.fulfilments.show.operations.invoices.all_invoices.show',
            'grp.org.fulfilments.show.operations.invoices.unpaid_invoices.show',
            'grp.org.fulfilments.show.operations.invoices.show' => [
                'label' => $invoice->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $invoice->organisation->slug,
                        'fulfilment'   => $this->parent->slug,
                        'invoice'      => $invoice->slug
                    ]

                ]
            ],

            //  'grp.org.fulfilments.show.crm.customers.show.refund.show'
            'grp.org.fulfilments.show.crm.customers.show.invoices.show' => [
                'label' => $invoice->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $invoice->organisation->slug,
                        'fulfilment'         => $invoice->shop->fulfilment->slug,
                        'fulfilmentCustomer' => $this->parent->slug,
                        'invoice'            => $invoice->slug
                    ]
                ]
            ],
        };
    }
}
