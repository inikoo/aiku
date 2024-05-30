<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\AttachRecurringBillToModel;
use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\RecurringBill\StoreRecurringBill;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;

class SetPalletDeliveryAsBookedIn extends OrgAction
{
    use WithActionUpdate;
    private PalletDelivery $palletDelivery;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        $modelData['booked_in_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::BOOKED_IN;


        foreach ($palletDelivery->pallets as $pallet) {
            if ($pallet->state == PalletStateEnum::BOOKED_IN) {
                UpdatePallet::run($pallet, [
                    'state'      => PalletStateEnum::STORING,
                    'status'     => PalletStatusEnum::STORING,
                    'storing_at' => now()
                ]);
            }
        }

        $palletDelivery = $this->update($palletDelivery, $modelData);

        $recurringBill = $palletDelivery->fulfilmentCustomer->currentRecurringBill;
        if(!$recurringBill) {
            $recurringBill=StoreRecurringBill::make()->action($palletDelivery->fulfilmentCustomer->rentalAgreement, [
                'start_date' => now(),
                'end_date'   => now()->addMonth(),
                'status'     => 'active'
            ]);
            $palletDelivery->fulfilmentCustomer->update(['current_recurring_bill_id' => $recurringBill->id]);
        }

        AttachRecurringBillToModel::run($palletDelivery, $recurringBill);

        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);
        SendPalletDeliveryNotification::dispatch($palletDelivery);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->palletDelivery->state != PalletDeliveryStateEnum::BOOKING_IN) {
            return false;
        }

        if($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function action(PalletDelivery $palletDelivery): PalletDelivery
    {
        $this->asAction       = true;
        $this->palletDelivery = $palletDelivery;
        $this->initialisation($palletDelivery->organisation, []);
        return $this->handle($palletDelivery);
    }


}
