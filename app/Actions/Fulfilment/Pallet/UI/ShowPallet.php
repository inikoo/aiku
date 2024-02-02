<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\UI\PalletTabsEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Pallet $pallet
 */
class ShowPallet extends OrgAction
{
    public Customer|null $customer = null;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.edit");

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.view");
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($pallet);
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($pallet);
    }

    public function handle(Pallet $pallet): Pallet
    {
        return $pallet;
    }


    public function htmlResponse(Pallet $pallet): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Pallet',
            [
                'title'       => __('pallets'),
                'breadcrumbs' => $this->getBreadcrumbs($this->pallet),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-narwhal'],
                            'title' => __('pallets')
                        ],
                    'title'  => $this->pallet->slug,
                    'actions'=> [
                        /*[
                            'type'    => 'button',
                            'style'   => 'cancel',
                            'tooltip' => __('return to customer'),
                            'label'   => __($this->pallet->status == PalletStatusEnum::RETURNED ? 'returned' : 'return to customer'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setReturn',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'disabled' => $this->pallet->status == PalletStatusEnum::RETURNED
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'edit',
                            'tooltip' => __('edit stored items'),
                            'label'   => __('stored items'),
                            'route'   => [
                                'name'       => preg_replace('/show$/', 'edit', request()->route()->getName()),
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'delete',
                            'tooltip' => __('set as damaged'),
                            'label'   => __($this->pallet->status == PalletStatusEnum::DAMAGED ? 'damaged' : 'set as damaged'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setDamaged',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'disabled' => $this->pallet->status == PalletStatusEnum::DAMAGED
                        ],*/
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PalletTabsEnum::navigation(),
                ],

                PalletTabsEnum::STORED_ITEMS->value => $this->tab == PalletTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::make(IndexStoredItems::run($pallet->fulfilmentCustomer))
                    : Inertia::lazy(fn () => StoredItemResource::make(IndexStoredItems::run($pallet->fulfilmentCustomer))),

                PalletTabsEnum::HISTORY->value => $this->tab == PalletTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($this->pallet))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($this->pallet)))

            ]
        )->table(IndexHistory::make()->tableStructure())
            ->table(IndexStoredItems::make()->tableStructure($pallet->items));
    }


    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }

    public function getBreadcrumbs(Pallet $pallet, $suffix = null): array
    {
        return [];

        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.fulfilment.stored-items.index',
                            ],
                            'label' => __('stored items')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.fulfilment.stored-items.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $pallet->slug,
                        ],
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }
}
