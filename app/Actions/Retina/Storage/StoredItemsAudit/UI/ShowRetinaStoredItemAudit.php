<?php
/*
 * author Arya Permana - Kirin
 * created on 13-01-2025-16h-41m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\StoredItemsAudit\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInAudit;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\StoredItemAuditResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;

class ShowRetinaStoredItemAudit extends RetinaAction
{
    use HasFulfilmentAssetsAuthorisation;

    private bool $selectStoredPallets = false;

    public function handle(StoredItemAudit $storedItemAudit): StoredItemAudit
    {
        return $storedItemAudit;
    }

    public function jsonResponse(StoredItemAudit $storedItemAudit): StoredItemAuditResource
    {
        return StoredItemAuditResource::make($storedItemAudit);
    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): Response
    {
        $subNavigation = [];

        $title      = __("Customer's SKUs audit");
        $icon       = ['fal', 'fa-pallet'];
        $afterTitle = null;
        $iconRight  = null;

        $actions = [];
        if ($storedItemAudit->state === StoredItemAuditStateEnum::IN_PROCESS) {
            $actions = [
                [
                    'type'  => 'button',
                    'style' => 'primary',
                    'label' => __('Complete Audit'),
                    'route' => [
                        'method' => 'patch',
                        'name'       => 'grp.models.fulfilment-customer.stored_item_audits.complete',
                        'parameters' => [
                            'fulfilmentCustomer' => $storedItemAudit->fulfilment_customer_id,
                            'storedItemAudit' => $storedItemAudit->id
                        ],
                    ]
                ]
            ];
        }

        return Inertia::render(
            'Storage/RetinaStoredItemsAudit',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __("Customer's skus audits"),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,
                    'actions' => $actions,
                    'subNavigation' => $subNavigation,
                ],

                'notes_data' => [
                    [
                        'label'    => __('Public'),
                        'note'     => $storedItemAudit->public_notes ?? '',
                        'editable' => true,
                        'bgColor'  => 'pink',
                        'field'    => 'public_notes'
                    ],
                    [
                        'label'    => __('Private'),
                        'note'     => $storedItemAudit->internal_notes ?? '',
                        'editable' => true,
                        'bgColor'  => 'purple',
                        'field'    => 'internal_notes'
                    ],
                ],

                // 'route' => [
                //     'update' => [
                //         'name'       => 'grp.models.fulfilment-customer.stored_item_audits.update',
                //         'parameters' => [
                //             'fulfilmentCustomer' => $storedItemAudit->fulfilment_customer_id,
                //             'storedItemAudit'    => $storedItemAudit->id
                //         ]
                //     ]
                // ],

                // 'storedItemsRoute' => [
                //     'index'  => [
                //         'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                //         'parameters' => [
                //             'organisation'       => $storedItemAudit->organisation->slug,
                //             'fulfilment'         => $storedItemAudit->fulfilment->slug,
                //             'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->slug,
                //             'palletDelivery'     => $storedItemAudit->reference
                //         ]
                //     ],
                //     'store'  => [
                //         'name'       => 'grp.models.fulfilment-customer.stored-items.store',
                //         'parameters' => [
                //             'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->id
                //         ]
                //     ],
                //     'delete' => [
                //         'name' => 'grp.models.stored-items.delete'
                //     ]
                // ],

                'data'                => StoredItemAuditResource::make($storedItemAudit),
                'pallets'             => PalletsResource::collection(IndexPalletsInAudit::run($storedItemAudit->fulfilmentCustomer, 'pallets')),
                'fulfilment_customer' => FulfilmentCustomerResource::make($storedItemAudit->fulfilmentCustomer)->getArray()
            ]
        )->table(
            IndexPalletsInAudit::make()->tableStructure(
                $storedItemAudit->fulfilmentCustomer,
                prefix: 'pallets'
            )
        );
    }

    public function asController(StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAudit
    {
        $this->initialisation($request);

        return $this->handle($storedItemAudit);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowRetinaBillingDashboard::make()->getBreadcrumbs(),
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-receipt',
                    'label' => __('stored items audits'),
                    'route' => [
                        'name' => 'retina.billing.next_recurring_bill'
                    ]
                ]

            ],
        );
    }
}
