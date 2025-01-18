<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Jan 2025 01:19:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\UI;

use App\Actions\Retina\UI\Dashboard\ShowRetinaDashboard;
use App\Actions\RetinaAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Catalogue\RetinaRentalAgreementResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRetinaStorageDashboard extends RetinaAction
{
    public function asController(ActionRequest $request): FulfilmentCustomer
    {
        $this->initialisation($request);
        return $this->customer->fulfilmentCustomer;
    }

    public function htmlResponse(FulfilmentCustomer $fulfilmentCustomer): Response
    {

        $clauses = null;
        foreach ($fulfilmentCustomer->rentalAgreementClauses as $clause) {
            $price                                  = $clause->asset->price;
            $percentageOff                          = $clause->percentage_off;
            $discount                               = $percentageOff / 100;
            $clauses[] = [
                'name'           => $clause->asset->name,
                'asset_id'       => $clause->asset_id,
                'type'           => $clause->asset->type->value,
                'agreed_price'   => $price - $price * $discount,
                'price'          => $price,
                'percentage_off' => $percentageOff
            ];
        }

        return Inertia::render('Storage/RetinaStorageDashboard', [
            'title'        => __('Storage Dashboard'),
            'breadcrumbs'    => $this->getBreadcrumbs(),
            'pageHead'    => [

                'title'         => __('Storage Dashboard'),
                'icon'          => [
                    'icon'  => ['fal', 'fa-tachometer-alt'],
                    'title' => __('Storage Dashboard')
                ],

            ],

            'currency'     => CurrencyResource::make($fulfilmentCustomer->fulfilment->shop->currency),
            'storageData'  => $this->getDashboardData($fulfilmentCustomer),
            'customer'     => CustomersResource::make($fulfilmentCustomer->customer)->resolve(),
            'rental_agreement' => RetinaRentalAgreementResource::make($fulfilmentCustomer->rentalAgreement),
            'discounts'    => $clauses
        ]);
    }

    public function getDashboardData(FulfilmentCustomer $fulfilmentCustomer): array
    {
        $stats = [];

        $stats['pallets'] = [
            'label'         => __('Pallet'),
            'count'         => $fulfilmentCustomer->number_pallets,
            'description'   => __('in warehouse'),
        ];

        foreach (PalletStateEnum::cases() as $case) {
            $stats['pallets']['state'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletStateEnum::stateIcon()[$case->value],
                'count' => PalletStateEnum::count($fulfilmentCustomer)[$case->value] ?? 0,
                'label' => PalletStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_deliveries'] = [
            'label' => __('Pallet Delivery'),
            'count' => $fulfilmentCustomer->number_pallet_deliveries
        ];
        foreach (PalletDeliveryStateEnum::cases() as $case) {
            $stats['pallet_deliveries']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletDeliveryStateEnum::stateIcon()[$case->value],
                'count' => PalletDeliveryStateEnum::count($fulfilmentCustomer)[$case->value],
                'label' => PalletDeliveryStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_returns'] = [
            'label' => __('Pallet Return'),
            'count' => $fulfilmentCustomer->number_pallet_returns
        ];
        foreach (PalletReturnStateEnum::cases() as $case) {
            $stats['pallet_returns']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletReturnStateEnum::stateIcon()[$case->value],
                'count' => PalletReturnStateEnum::count($fulfilmentCustomer)[$case->value],
                'label' => PalletReturnStateEnum::labels()[$case->value]
            ];
        }

        return $stats;
    }

    public function getBreadcrumbs(): array
    {

        return
            array_merge(
                ShowRetinaDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'retina.fulfilment.storage.dashboard'
                            ],
                            'label' => __('Storage'),
                        ]
                    ]
                ]
            );

    }
}
