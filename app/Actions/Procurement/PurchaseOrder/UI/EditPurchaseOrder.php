<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditPurchaseOrder extends OrgAction
{
    use WithProcurementEditAuthorisation;
    use WithAgentOrganisation;

    private bool $actingAsAgent = false;

    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder;
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->initialisation($organisation, $request);
        if ($purchaseOrder->organisation_id !== $organisation->id) {
            abort_unless($this->agentEditsOwnOrder($purchaseOrder, $request->user()), 404);
            $this->actingAsAgent = true;
        }

        return $this->handle($purchaseOrder);
    }

    public function htmlResponse(PurchaseOrder $purchaseOrder, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('purchase order'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $purchaseOrder,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'     => $purchaseOrder->reference,
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.show',
                                'parameters' => [$this->organisation->slug, $purchaseOrder->slug]
                            ]
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => $this->actingAsAgent ? $this->agentSections($purchaseOrder) : [
                        [
                            'label'  => __('Reference'),
                            'title'  => __('Reference'),
                            'icon'   => 'fal fa-fingerprint',
                            'fields' => [
                                'reference' => [
                                    'type'     => 'input',
                                    'label'    => __('reference'),
                                    'required' => true,
                                    'value'    => $purchaseOrder->reference
                                ],
                            ]
                        ],
                        [
                            'label'  => __('Delivery'),
                            'title'  => __('Delivery'),
                            'icon'   => 'fal fa-truck',
                            'fields' => [
                                'delivery_address' => [
                                    'type'        => 'textarea',
                                    'label'       => __('Deliver to'),
                                    'information' => __('The address the supplier ships the goods to, printed on the purchase order.'),
                                    'placeholder' => __('Leave empty to use the warehouse address'),
                                    'value'       => Arr::get($purchaseOrder->data, 'delivery_address'),
                                ],
                            ]
                        ],
                        ...$this->termsSections($purchaseOrder),
                        ...$this->productionSections($purchaseOrder),
                        ...$this->cleanHandoverSections($purchaseOrder, $request),
                        [
                            'label'  => __('Payments'),
                            'title'  => __('Payments'),
                            'icon'   => 'fal fa-money-bill',
                            'fields' => [
                                'deposit_amount'  => $this->depositAmountField($purchaseOrder),
                                'balance_paid_at' => [
                                    'type'        => 'date',
                                    'label'       => __('Balance paid'),
                                    'information' => __('The day the rest of the order was paid to the supplier.'),
                                    'value'       => $purchaseOrder->balance_paid_at,
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.purchase-order.update',
                            'parameters' => $purchaseOrder->id

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(PurchaseOrder $purchaseOrder, string $routeName, array $routeParameters): array
    {
        return ShowPurchaseOrder::make()->getBreadcrumbs(
            purchaseOrder: $purchaseOrder,
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    /**
     * What an agent records on an order placed through it.
     *
     * @return array<int, array<string, mixed>>
     */
    private function agentSections(PurchaseOrder $purchaseOrder): array
    {
        return [
            ...array_values(array_filter(
                $this->termsSections($purchaseOrder),
                fn (array $section) => Arr::has($section, 'fields.estimated_production_date')
            )),
            ...$this->productionSections($purchaseOrder),
            [
                'label'  => __('Clean handover'),
                'title'  => __('Clean handover'),
                'icon'   => 'fal fa-clipboard-list',
                'fields' => [
                    'proposed_ready_at' => [
                        'type'        => 'date',
                        'label'       => __('Proposed ready date'),
                        'information' => __('The day the buyer or the agent expects the goods to be ready to hand over. Management confirms it as the approved ready date.'),
                        'value'       => $purchaseOrder->proposed_ready_at,
                    ],
                ],
            ],
            [
                'label'  => __('Payments'),
                'title'  => __('Payments'),
                'icon'   => 'fal fa-money-bill',
                'fields' => [
                    'deposit_amount'  => $this->depositAmountField($purchaseOrder),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function depositAmountField(PurchaseOrder $purchaseOrder): array
    {
        return [
            'type'        => 'input_number',
            'label'       => __('Deposit amount'),
            'information' => __('The deposit paid to the supplier before production, in the currency of the order.'),
            'bind'        => [
                'mode'              => 'currency',
                'currency'          => $purchaseOrder->currency->code,
                'min'               => 0,
                'step'              => 0.25,
                'minFractionDigits' => 2,
                'maxFractionDigits' => 2,
            ],
            'value'       => $purchaseOrder->deposit_amount === null ? null : (float) $purchaseOrder->deposit_amount,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productionSections(PurchaseOrder $purchaseOrder): array
    {
        if (!$purchaseOrder->isAgentOrder()) {
            return [];
        }

        return [
            [
                'label'  => __('Production'),
                'title'  => __('Production'),
                'icon'   => 'fal fa-industry',
                'fields' => [
                    'produced_at'        => [
                        'type'        => 'date',
                        'label'       => __('Production done'),
                        'information' => __('The day the supplier finished making the goods. Ticks the Production step of the order journey.'),
                        'value'       => $purchaseOrder->produced_at,
                    ],
                ],
            ],
        ];
    }

    /**
     * The delivery, payment and label terms staff used to edit from the purchase order page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function termsSections(PurchaseOrder $purchaseOrder): array
    {
        $icons = [
            'delivery_type'             => 'fal fa-truck-container',
            'estimated_production_date' => 'fal fa-calendar-check',
            'payment_terms'             => 'fal fa-file-invoice',
            'incoterm'                  => 'fal fa-file-signature',
            'terms_and_conditions'      => 'fal fa-tags',
        ];

        return collect(GetPurchaseOrderData::run($purchaseOrder)['blueprint'])
            ->map(fn (array $section) => [
                'label'  => $section['title'],
                'title'  => $section['title'],
                'icon'   => $icons[array_key_first($section['fields'])] ?? 'fal fa-info-circle',
                'fields' => Arr::except($section['fields'], ['reference', 'delivery_address']),
            ])
            ->filter(fn (array $section) => $section['fields'])
            ->values()
            ->all();
    }

    /**
     * An agent order's clean handover dates: the ready date is proposed by the buyer or the agent and approved by management, who also record compliance and any exclusion from the score.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cleanHandoverSections(PurchaseOrder $purchaseOrder, ActionRequest $request): array
    {
        if (!$purchaseOrder->isAgentOrder()) {
            return [];
        }

        $fields = [
            'proposed_ready_at' => [
                'type'        => 'date',
                'label'       => __('Proposed ready date'),
                'information' => __('The day the buyer or the agent expects the goods to be ready to hand over. Management confirms it as the approved ready date.'),
                'value'       => $purchaseOrder->proposed_ready_at,
            ],
        ];

        if ($request->user()->authorisedShopOrganisations()->exists()) {
            $fields += [
                'approved_ready_at'      => [
                    'type'        => 'date',
                    'label'       => __('Approved ready date'),
                    'information' => __('The ready date management agreed. The handover counts as clean when it happens within 7 days of this date, and the order counts in the quarter of this date.'),
                    'value'       => $purchaseOrder->approved_ready_at,
                ],
                'handed_over_at'         => [
                    'type'        => 'date',
                    'label'       => __('Clean handover'),
                    'information' => __('The day the goods were handed over complete, checked and with their paperwork. Ticks the Clean handover step of the order journey.'),
                    'value'       => $purchaseOrder->handed_over_at,
                ],
                'compliance_complete_at' => [
                    'type'        => 'date',
                    'label'       => __('Compliance complete'),
                    'information' => __('The day every compliance document (certificates, test reports, labels) was received and checked. Needed for a clean handover.'),
                    'value'       => $purchaseOrder->compliance_complete_at,
                ],
                'chs_excluded'           => [
                    'type'        => 'toggle',
                    'label'       => __('Exclude from clean handover score'),
                    'information' => __('Turn on to leave this order out of the agent\'s clean handover score, e.g. when the delay was our fault. Write why in the exclusion reason.'),
                    'value'       => $purchaseOrder->chs_excluded,
                ],
                'chs_exclusion_reason'   => [
                    'type'        => 'input',
                    'label'       => __('Exclusion reason'),
                    'information' => __('Why this order is left out of the clean handover score.'),
                    'value'       => $purchaseOrder->chs_exclusion_reason,
                ],
            ];
        }

        return [
            [
                'label'  => __('Clean handover'),
                'title'  => __('Clean handover'),
                'icon'   => 'fal fa-clipboard-list',
                'fields' => $fields,
            ],
        ];
    }
}
