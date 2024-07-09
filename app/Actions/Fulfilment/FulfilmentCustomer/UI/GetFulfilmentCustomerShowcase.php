<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Catalogue\RentalAgreementResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFulfilmentCustomerShowcase
{
    use AsObject;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): array
    {
        $irisDomain = $fulfilmentCustomer->fulfilment->shop?->website?->domain;

        $recurringBillData= [];

        if ($fulfilmentCustomer->currentRecurringBill) {
            $recurringBillData = [
                'route'      => [
                    'name'             => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                    'parameters' => [
                        'organisation'       => $fulfilmentCustomer->organisation->slug,
                        'fulfilment'         => $fulfilmentCustomer->fulfilment->slug,
                        'fulfilmentCustomer' => $fulfilmentCustomer->slug,
                        'recurringBill'      => $fulfilmentCustomer->currentRecurringBill->slug,
                        ]
                    ],
                'label'         => 'Recurring Bills',
                'start_date'    => $fulfilmentCustomer->currentRecurringBill->start_date ?? '',
                'end_date'      => $fulfilmentCustomer->currentRecurringBill->end_date ?? '', 
                'currency_code' => 'usd',
                'status'        => $fulfilmentCustomer->currentRecurringBill->status ?? '' 
            ];
        }

        return [
            'customer'                     => CustomersResource::make($fulfilmentCustomer->customer)->getArray(),
            'fulfilment_customer'          => FulfilmentCustomerResource::make($fulfilmentCustomer)->getArray(),
            'rental_agreement'             => [
                'stats'                         => $fulfilmentCustomer->rentalAgreement ? RentalAgreementResource::make($fulfilmentCustomer->rentalAgreement) : false,
                'createRoute'                   => [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.rental-agreement.create',
                    'parameters' => array_values($request->route()->originalParameters())
                ],
            ],
            'recurring_bill'      => $recurringBillData,
            'updateRoute'                  => [
                'name'       => 'grp.models.fulfilment-customer.update',
                'parameters' => [$fulfilmentCustomer->id]
            ],
            'pieData'               => $this->getDashboardData($fulfilmentCustomer),
            'warehouse_summary'     => [
                'pallets_stored'    => $fulfilmentCustomer->fulfilment->warehouses->sum(function (Warehouse $warehouse) {
                    return $warehouse->stats->number_pallets;
                }),
                'total_items'       => $fulfilmentCustomer->fulfilment->warehouses->sum(function (Warehouse $warehouse) {
                    return $warehouse->stats->number_stored_items;
                })
            ],
            'webhook'               => [
                'webhook_access_key'    => $fulfilmentCustomer->webhook_access_key,
                'domain'                => (app()->environment('local') ? 'http://' : 'https://') . $irisDomain.'/webhooks/',
                'route'                 => [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.webhook.fetch',
                    'parameters' => array_values($request->route()->originalParameters())
                ],
            ]
        ];
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
}
