<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-09h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\UI\Fulfilment\ShowFulfilmentDashboard;
use App\Actions\UI\Retina\Storage\UI\ShowRetinaStorageDashboard;
use App\Enums\UI\Fulfilment\PalletTabsEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Http\Resources\Fulfilment\RetinaPalletResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Pallet $pallet
 */
class ShowRetinaPallet extends RetinaAction
{
    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->initialisation($request)->withTab(PalletTabsEnum::values());

        return $this->handle($pallet);
    }

    public function handle(Pallet $pallet): Pallet
    {
        return $pallet;
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): Response
    {
        // dd($pallet->status->statusIcon()[$pallet->status->value]);
        $icon = [
            'icon'    => ['fal', 'fa-pallet'],
            'tooltip' => __('Pallet')
        ];
        $model = __('Pallet');
        $title = $this->pallet->reference;
        $iconRight = $pallet->status->statusIcon()[$pallet->status->value];
        $afterTitle = [
            'label'     => '(' . $this->pallet->customer_reference . ')'
        ];

        $navigation = PalletTabsEnum::navigation($pallet);

        if ($pallet->number_stored_items == 0) {
            unset($navigation[PalletTabsEnum::STORED_ITEMS->value]);
        }

        $routeName = null;

        return Inertia::render(
            'Storage/RetinaPallet',
            [
                'title'                         => __('pallets'),
                'breadcrumbs'                   => $this->getBreadcrumbs(
                    $pallet,
                    request()->route()->getName(),
                    request()->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($pallet, $request),
                    'next'     => $this->getNext($pallet, $request),
                ],
                'pageHead'                      => [
                    'icon'          => $icon,
                    'title'         => $title,
                    'model'         => $model,
                    'iconRight'     => $iconRight,
                    'noCapitalise'  => true,
                    'afterTitle'    => $afterTitle,
                    'actions'       => [
                        // [
                        //     'type'    => 'button',
                        //     'style'   => 'cancel',
                        //     'tooltip' => __('return to customer'),
                        //     'label'   => $this->pallet->status == PalletStatusEnum::RETURNED ? __('returned') : __('return to customer'),
                        //     'route'   => [
                        //         'name'       => 'grp.fulfilment.stored-items.setReturn',
                        //         'parameters' => array_values(request()->route()->originalParameters())
                        //     ],
                        //     'disabled' => $this->pallet->status == PalletStatusEnum::RETURNED
                        // ],
                        [
                            'type'    => 'button',
                            'style'   => 'edit',
                            'tooltip' => __('edit pallet'),
                            'label'   => __('Edit'),
                            'route'   => [
                                'name'       => $routeName,
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ],
                        // [
                        //     'type'    => 'button',
                        //     'style'   => 'delete',
                        //     'tooltip' => __('set as damaged'),
                        //     'label'   => $this->pallet->status == PalletStatusEnum::DAMAGED ? __('damaged') : __('set as damaged'),
                        //     'route'   => [
                        //         'name'       => 'grp.fulfilment.stored-items.setDamaged',
                        //         'parameters' => array_values(request()->route()->originalParameters())
                        //     ],
                        //     'disabled' => $this->pallet->status == PalletStatusEnum::DAMAGED
                        // ],
                    ],
                ],
                'tabs'                          => [
                    'current'    => $this->tab,
                    'navigation' => $navigation,
                ],
                PalletTabsEnum::SHOWCASE->value => $this->tab == PalletTabsEnum::SHOWCASE->value ?
                    fn () => $this->jsonResponse($pallet) : Inertia::lazy(fn () => $this->jsonResponse($pallet)),

                PalletTabsEnum::STORED_ITEMS->value => $this->tab == PalletTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItems::run($pallet->fulfilmentCustomer, PalletTabsEnum::STORED_ITEMS->value))
                    : Inertia::lazy(fn () => StoredItemResource::collection(IndexStoredItems::run($pallet->fulfilmentCustomer, PalletTabsEnum::STORED_ITEMS->value))),

                PalletTabsEnum::HISTORY->value => $this->tab == PalletTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($this->pallet))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($this->pallet)))

            ]
        )->table(IndexHistory::make()->tableStructure(prefix: PalletTabsEnum::HISTORY->value))
            ->table(IndexStoredItems::make()->tableStructure($pallet->storedItems, prefix: PalletTabsEnum::STORED_ITEMS->value));
    }


    public function jsonResponse(Pallet $pallet): RetinaPalletResource
    {
        return new RetinaPalletResource($pallet);
    }


    public function getBreadcrumbs(Pallet $pallet, string $routeName, $suffix = null): array
    {
        return array_merge(
            ShowRetinaStorageDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'retina.storage.pallets.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('Pallets')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'retina.storage.pallets.show',
                                'parameters' => [
                                    'pallet' => $pallet->slug
                                ]
                            ],
                            'label' => $pallet->reference,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }

    public function getPrevious(Pallet $pallet, ActionRequest $request): ?array
    {
        $previous = Pallet::where('id', '<', $pallet->id)
            ->where('fulfilment_customer_id', $request->user()->customer->fulfilmentCustomer->id)
            ->whereNotNull('slug')->orderBy('id', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Pallet $pallet, ActionRequest $request): ?array
    {
        $next = Pallet::where('id', '>', $pallet->id)
            ->where('fulfilment_customer_id', $request->user()->customer->fulfilmentCustomer->id)
            ->whereNotNull('slug')->orderBy('id')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Pallet $pallet, string $routeName): ?array
    {
        if (!$pallet) {
            return null;
        }

        return match ($routeName) {
            'retina.storage.pallets.show' => [
                'label' => $pallet->slug,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'pallet'             => $pallet->slug
                    ]
                ]
            ],
            
            default => null,
        };
    }
}
