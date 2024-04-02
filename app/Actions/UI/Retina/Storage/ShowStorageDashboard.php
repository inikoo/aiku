<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 14:12:54 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Storage;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\CRM\CustomersResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowStorageDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {
        return Inertia::render('Storage/StorageDashboard', [
            'title'    => __('Storage'),
            'pieData'  => $this->getDashboardData($request->user()->customer->fulfilmentCustomer),
            'customer' => CustomersResource::make($request->user()->customer)->resolve()
        ]);
    }

    public function getDashboardData(FulfilmentCustomer $parent): array
    {
        $stats = [];

        $stats['pallets'] = [
            'label' => __('Pallet'),
            'count' => $parent->number_pallets
        ];

        foreach (PalletStateEnum::cases() as $case) {
            $stats['pallets']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletStateEnum::stateIcon()[$case->value],
                'count' => PalletStateEnum::count($parent)[$case->value],
                'label' => PalletStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_delivery'] = [
            'label' => __('Pallet Delivery'),
            'count' => $parent->number_pallet_deliveries
        ];
        foreach (PalletDeliveryStateEnum::cases() as $case) {
            $stats['pallet_delivery']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletDeliveryStateEnum::stateIcon()[$case->value],
                'count' => PalletDeliveryStateEnum::count($parent)[$case->value],
                'label' => PalletDeliveryStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_return'] = [
            'label' => __('Pallet Return'),
            'count' => $parent->number_pallet_returns
        ];
        foreach (PalletReturnStateEnum::cases() as $case) {
            $stats['pallet_return']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletReturnStateEnum::stateIcon()[$case->value],
                'count' => PalletReturnStateEnum::count($parent)[$case->value],
                'label' => PalletReturnStateEnum::labels()[$case->value]
            ];
        }

        return $stats;
    }

    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-home',
                    'label' => $label,
                    'route' => [
                        'name' => 'retina.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
