<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrderTransaction\UI\IndexPurchaseOrderTransactions;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Enums\UI\Procurement\PurchaseOrderTabsEnum;
use App\Http\Resources\CRM\CustomerResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\PurchaseOrderTransactionResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Helpers\Address;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPurchaseOrder extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->initialisation($organisation, $request)->withTab(PurchaseOrderTabsEnum::values());

        return $purchaseOrder;
    }

    public function maya(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->maya   = true;
        $this->initialisation($organisation, $request)->withTab(PurchaseOrderTabsEnum::values());
        return $this->handle($purchaseOrder);
    }

    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder;
    }

    public function htmlResponse(PurchaseOrder $purchaseOrder, ActionRequest $request): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'Procurement/PurchaseOrder',
            [
                'title'       => __('purchase order'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $purchaseOrder,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($purchaseOrder, $request),
                    'next'     => $this->getNext($purchaseOrder, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'clipboard-list'],
                            'title' => __('purchase order')
                        ],
                    'title' => $purchaseOrder->reference,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,

                ],
                'routes'      => [
                    'updateOrderRoute' => [
                        'method'     => 'patch',
                        'name'       => 'grp.models.order.update',
                        'parameters' => [
                            'order' => $purchaseOrder->id,
                        ]
                    ],
                    'products_list'    => [
                        'name'       => 'grp.json.shop.catalogue.order.products',
                        // 'parameters' => [
                        //     'shop'  => $purchaseOrder->shop->slug,
                        //     'scope' => $purchaseOrder->slug
                        // ]
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PurchaseOrderTabsEnum::navigation()
                ],

                'timelines'   => 'TOOOOOOOOOOOOO DOOOOOOOOOOOOOOOOOOOOOOO',

                'notes'       => [
                    "note_list" => [
                        [
                            "label"    => __("Customer"),
                            "note"     => $purchaseOrder->customer_notes ?? '',
                            "editable" => false,
                            "bgColor"  => "#FF7DBD",
                            "field"    => "customer_notes"
                        ],
                        [
                            "label"    => __("Public"),
                            "note"     => $purchaseOrder->public_notes ?? '',
                            "editable" => true,
                            "bgColor"  => "#94DB84",
                            "field"    => "public_notes"
                        ],
                        [
                            "label"    => __("Private"),
                            "note"     => $purchaseOrder->internal_notes ?? '',
                            "editable" => true,
                            "bgColor"  => "#FCF4A3",
                            "field"    => "internal_notes"
                        ]
                    ]
                ],

                'box_stats'      => [
                    // 'customer'      => array_merge(
                    //     CustomerResource::make($purchaseOrder->customer)->getArray(),
                    //     [
                    //         'addresses' => [
                    //             'delivery' => AddressResource::make($purchaseOrder->deliveryAddress ?? new Address()),
                    //             'billing'  => AddressResource::make($purchaseOrder->billingAddress ?? new Address())
                    //         ],
                    //     ]
                    // ),
                    // 'products'      => [
                    //     'payment'          => [
                    //         'routes'       => [
                    //             'fetch_payment_accounts' => [
                    //                 'name'       => 'grp.json.shop.payment-accounts',
                    //                 'parameters' => [
                    //                     'shop' => $purchaseOrder->shop->slug
                    //                 ]
                    //             ],
                    //             'submit_payment'         => [
                    //                 'name'       => 'grp.models.customer.payment.order.store',
                    //                 'parameters' => [
                    //                     'customer' => $purchaseOrder->customer_id,
                    //                     'scope'    => $purchaseOrder->id
                    //                 ]
                    //             ]

                    //         ],
                    //         'total_amount' => (float) $purchaseOrder->total_amount,
                    //         'paid_amount'  => (float) $purchaseOrder->payment_amount,
                    //         'pay_amount'   => 33333333333333333333333333333333333333333333333,
                    //     ],
                    //     'estimated_weight' => 2222222222222222222222222222222222222222222222222222
                    // ],

                    'order_summary' => [
                        [
                            [
                                'label'       => 'Items',
                                'quantity'    => 99999999999999999999999999999999999,
                                'price_base'  => 'Multiple',
                                'price_total' => $purchaseOrder->net_amount
                            ],
                        ],
                        [
                            [
                                'label'       => 'Charges',
                                'information' => '',
                                'price_total' => '0'
                            ],
                            [
                                'label'       => 'Shipping',
                                'information' => '',
                                'price_total' => '0'
                            ]
                        ],
                        [
                            [
                                'label'       => 'Net',
                                'information' => '',
                                'price_total' => $purchaseOrder->net_amount
                            ],
                            [
                                'label'       => 'Tax 20%',
                                'information' => '',
                                'price_total' => $purchaseOrder->tax_amount
                            ]
                        ],
                        [
                            [
                                'label'       => 'Total',
                                'price_total' => $purchaseOrder->total_amount
                            ]
                        ],
                        'currency' => CurrencyResource::make($purchaseOrder->currency),
                    ],
                ],

                PurchaseOrderTabsEnum::SHOWCASE->value => $this->tab == PurchaseOrderTabsEnum::SHOWCASE->value ?
                    fn () => new PurchaseOrderResource(($purchaseOrder))
                    : Inertia::lazy(fn () => new PurchaseOrderResource(($purchaseOrder))),

                PurchaseOrderTabsEnum::ITEMS->value => $this->tab == PurchaseOrderTabsEnum::ITEMS->value ?
                    fn () => PurchaseOrderTransactionResource::collection(IndexPurchaseOrderTransactions::run($purchaseOrder))
                    : Inertia::lazy(fn () => PurchaseOrderTransactionResource::collection(IndexPurchaseOrderTransactions::run($purchaseOrder))),

                PurchaseOrderTabsEnum::HISTORY->value => $this->tab == PurchaseOrderTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($purchaseOrder))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($purchaseOrder)))
            ]
        )->table(IndexPurchaseOrderTransactions::make()->tableStructure(prefix: PurchaseOrderTabsEnum::ITEMS->value))
            ->table(IndexHistory::make()->tableStructure(prefix: PurchaseOrderTabsEnum::HISTORY->value));
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }

    public function getBreadcrumbs(PurchaseOrder $purchaseOrder, string $routeName, array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.index',
                                'parameters' => $routeParameters['organisation']
                            ],
                            'label' => __('Purchase order')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $purchaseOrder->reference,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(PurchaseOrder $purchaseOrder, ActionRequest $request): ?array
    {
        $previous = PurchaseOrder::where('reference', '<', $purchaseOrder->reference)->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PurchaseOrder $purchaseOrder, ActionRequest $request): ?array
    {
        $next = PurchaseOrder::where('reference', '>', $purchaseOrder->reference)->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PurchaseOrder $purchaseOrder, string $routeName): ?array
    {
        if (!$purchaseOrder) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.purchase_orders.show' => [
                'label' => $purchaseOrder->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $purchaseOrder->organisation,
                        'purchaseOrder' => $purchaseOrder->slug
                    ]

                ]
            ]
        };
    }
}
