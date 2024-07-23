<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 21:27:03 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatus;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilmentCustomerStatus extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomer
    {
        $status    = FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT;
        $createdAt = $fulfilmentCustomer->rentalAgreement->created_at;

        $palletStoringExists = $fulfilmentCustomer->pallets()
            ->where('state', PalletStateEnum::STORING->value)
            ->exists();

        if($fulfilmentCustomer->rentalAgreement
            && $createdAt->lessThan($createdAt->addDays(30))
            && $palletStoringExists) {
            $status = FulfilmentCustomerStatus::ACTIVE;
        }

        $fulfilmentCustomer->updateQuietly([
            'status' => $status
        ]);

        return $fulfilmentCustomer;
    }

    protected function updateNoRentalAgreementStatus(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $fulfilmentCustomer->customer->updateQuietly(['status' => FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT->value]);
    }

    protected function updateActiveStatus(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $fulfilmentCustomer->customer->where(function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subMonth())
                ->orWhereHas('pallets', function ($query) {
                    $query->where('status', PalletStatusEnum::STORING->value);
                })
                ->orWhere(function ($query) {
                    $query->whereDoesntHave('pallets', function ($query) {
                        $query->where('status', PalletStatusEnum::STORING->value);
                    })->where(function ($query) {
                        $query->where('last_dispatched_delivery_at', '>=', Carbon::now()->subMonth())
                            ->orWhere('last_submitted_order_at', '>=', Carbon::now()->subMonth())
                            ->orWhere('last_invoiced_at', '>=', Carbon::now()->subMonth())
                            ->orWhereHas('recurring_bills', function ($query) {
                                $query->where('end_date', '>=', Carbon::now()->subMonth());
                            });
                    });
                });
        })->updateQuietly(['status' => FulfilmentCustomerStatus::ACTIVE->value]);
    }

    protected function updateInactiveStatus(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $fulfilmentCustomer->customer->where(function ($query) {
            $query->where('last_dispatched_delivery_at', '>=', Carbon::now()->subMonths(2))
                ->orWhere('last_submitted_order_at', '>=', Carbon::now()->subMonths(2))
                ->orWhere('last_invoiced_at', '>=', Carbon::now()->subMonths(2))
                ->orWhereHas('recurring_bills', function ($query) {
                    $query->where('end_date', '>=', Carbon::now()->subMonths(2));
                });
        })->updateQuietly(['status' => FulfilmentCustomerStatus::INACTIVE->value]);
    }

    protected function updateLostStatus(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $fulfilmentCustomer->customer->where(function ($query) {
            $query->where('last_pallet_deliver', '<', Carbon::now()->subMonths(2))
                ->orWhere('last_return', '<', Carbon::now()->subMonths(2))
                ->orWhere('last_invoice', '<', Carbon::now()->subMonths(2))
                ->orWhereHas('recurring_bills', function ($query) {
                    $query->where('end_date', '<', Carbon::now()->subMonths(2));
                });
        })->updateQuietly(['status' => FulfilmentCustomerStatus::LOST->value]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(
        Organisation $organisation,
        Shop $shop,
        Fulfilment $fulfilment,
        FulfilmentCustomer $fulfilmentCustomer,
        ActionRequest $request
    ): FulfilmentCustomer {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function action(
        FulfilmentCustomer $fulfilmentCustomer,
        array $modelData
    ): FulfilmentCustomer {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer);
    }
}
