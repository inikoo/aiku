<?php
/*
 * author Arya Permana - Kirin
 * created on 21-02-2025-08h-27m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\StoredItemAuditDelta\UI\IndexStoredItemAuditDeltas;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\StoredItemAuditDeltasResource;
use App\Http\Resources\Fulfilment\StoredItemAuditResource;
use App\Http\Resources\Fulfilment\StoredItemDeltasInProcessResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Fulfilment\StoredItemAuditDelta;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowStoredItemAuditForPallet extends OrgAction
{
    use WithFulfilmentAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    private Fulfilment|Location|FulfilmentCustomer $parent;
    private Pallet $pallet;

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
        $render = Inertia::render(
            'Org/Fulfilment/PalletAudit',
            [
                'title'       => __('pallet audit'),
                // 'breadcrumbs' => $this->getBreadcrumbs(
                //     $request->route()->getName(),
                //     $request->route()->originalParameters()
                // ),
                // 'navigation' => [
                //     'previous' => $this->getPrevious($palletReturn, $request),
                //     'next'     => $this->getNext($palletReturn, $request),
                // ],
                'pageHead' => [
                    // 'container' => $container,
                    // 'subNavigation' => $subNavigation,
                    'title'     => __('Audit'),
                    'model'     => __('Pallet'),
                    // 'afterTitle' => $afterTitle,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-pallet'],
                        'title' => __('Pallet')
                    ],
                    // 'edit' => $this->canEdit ? [
                    //     'route' => [
                    //         'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                    //         'parameters' => array_values($request->route()->originalParameters())
                    //     ]
                    // ] : false,
                    // 'actions' => $actions
                ],
                'edit_stored_item_deltas' => null,
                'stored_item_deltas' => null,
                'route_list' => [
                    // 'update' => [
                    //     'name'       => 'grp.models.stored_item_audit.update',
                    //     'parameters' => [
                    //         'storedItemAudit' => $storedItemAudit->id
                    //     ]
                    // ],
                    // 'stored_item_audit_delta' => [
                    //     'update' => [  // Update quantity
                    //         'method'     => 'patch',
                    //         'name'       => 'grp.models.stored_item_audit_delta.update',
                    //         //parameters: add the storedItemAuditDelta id in the FE
                    //     ],
                    //     'delete' => [
                    //         'method'     => 'delete',
                    //         'name'       => 'grp.models.stored_item_audit_delta.delete',
                    //         //parameters: add the storedItemAuditDelta id in the FE
                    //     ],
                    //     'store' => [
                    //         'method'     => 'post',
                    //         'name'       => 'grp.models.stored_item_audit.stored_item_audit_delta.store',
                    //         'parameters' => [
                    //             $storedItemAudit->id
                    //         ]
                    //     ],
                    // ]
                ],
            ]
        );

        return $render;

    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPalletInFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Pallet $pallet, StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAudit
    {
        $this->parent = $fulfilment;
        $this->pallet = $pallet;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($storedItemAudit);
    }
}
