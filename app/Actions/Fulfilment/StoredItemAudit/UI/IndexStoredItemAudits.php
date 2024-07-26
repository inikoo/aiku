<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
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

class IndexStoredItemAudits extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    private Fulfilment|Location $parent;

    private bool $selectStoredPallets = false;

    public function handle(Fulfilment|FulfilmentCustomer|Warehouse $parent, $prefix = null): StoredItemAudit
    {
        return $parent->storedItemAudit;
    }

    public function jsonResponse(StoredItemAudit $storedItemAudit): StoredItemAuditResource
    {
        return StoredItemAuditResource::make($storedItemAudit);
    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): Response
    {
        $subNavigation = [];

        $title      = __('Stored Item Audits');
        $icon       = ['fal', 'fa-pallet'];
        $afterTitle = null;
        $iconRight  = null;

        return Inertia::render(
            'Org/Fulfilment/StoredItemAudits',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Stored Item Audits'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,

                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('New Audit'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-item-audits.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ],
                    ],
                ],

                'notes_data'             => [
                    [
                        'label'           => __('Public'),
                        'note'            => $storedItemAudit->public_notes ?? '',
                        'editable'        => true,
                        'bgColor'         => 'pink',
                        'field'           => 'public_notes'
                    ],
                    [
                        'label'           => __('Private'),
                        'note'            => $storedItemAudit->internal_notes ?? '',
                        'editable'        => true,
                        'bgColor'         => 'purple',
                        'field'           => 'internal_notes'
                    ],
                ],

                'showcase'            => StoredItemAuditResource::make($storedItemAudit),
                'pallets'             => PalletsResource::collection($storedItemAudit->fulfilmentCustomer->pallets),
                'fulfilment_customer' => FulfilmentCustomerResource::make($storedItemAudit->fulfilmentCustomer)->getArray()
            ]
        )->table(
            IndexPalletsInCustomer::make()->tableStructure(
                $storedItemAudit->fulfilmentCustomer,
                prefix: 'pallets'
            )
        );
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): StoredItemAudit
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, 'stored_item_audits');
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItemAudit
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, 'stored_item_audits');
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilments.show.operations.stored-item-audits.index',
                            'parameters' => [
                                'organisation' => $routeParameters['organisation'],
                                'fulfilment'   => $routeParameters['fulfilment'],
                            ]
                        ],
                        'label' => __('Stored Item Audits'),
                        'icon'  => 'fal fa-bars',
                    ],
                ]
            ]
        );
    }
}
