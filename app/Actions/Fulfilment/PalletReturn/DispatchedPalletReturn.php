<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class DispatchedPalletReturn extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $modelData[PalletReturnStateEnum::DISPATCHED->value.'_at'] = now();
        $modelData['state']                                        = PalletReturnStateEnum::DISPATCHED;

        foreach ($palletReturn->pallets()
                     ->whereNot('status', PalletStatusEnum::INCIDENT->value)
                     ->get() as $pallet) {
            UpdatePallet::run($pallet, [
                'state'  => PalletStateEnum::DISPATCHED,
                'status' => PalletStatusEnum::RETURNED
            ]);

            $palletReturn->pallets()->syncWithoutDetaching([
                $pallet->id => [
                    'state' => PalletReturnStateEnum::DISPATCHED
                ]
            ]);
        }

        $palletReturn = $this->update($palletReturn, $modelData);

        HydrateFulfilmentCustomer::dispatch($palletReturn->fulfilmentCustomer);
        SendPalletReturnNotification::run($palletReturn);

        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletReturn $palletReturn): JsonResource
    {
        return new PalletReturnResource($palletReturn);
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn): PalletReturn
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, []);

        return $this->handle($palletReturn, $this->validatedData);
    }
}
