<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletReturn\Notifications\SendPalletReturnNotification;
use App\Actions\Fulfilment\PalletReturn\Search\PalletReturnRecordSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitPalletReturn extends OrgAction
{
    use WithActionUpdate;


    private bool $sendNotifications=false;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $modelData[PalletReturnStateEnum::SUBMITTED->value.'_at'] = now();

        if(!request()->user() instanceof WebUser) {
            $modelData[PalletReturnStateEnum::CONFIRMED->value.'_at'] = now();
            $modelData['state']                                       = PalletReturnStateEnum::CONFIRMED;
        } else {
            $modelData['state'] = PalletReturnStateEnum::SUBMITTED;
        }

        foreach ($palletReturn->pallets as $pallet) {
            UpdatePallet::run($pallet, [
                'reference' => GetSerialReference::run(
                    container: $palletReturn->fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET
                ),
                'state'  => $modelData['state']->value,
                'status' => PalletStatusEnum::RECEIVING
            ]);

            $palletReturn->pallets()->syncWithoutDetaching([$pallet->id => [
                'state' => $modelData['state']
            ]]);
        }

        $palletReturn = $this->update($palletReturn, $modelData);

        HydrateFulfilmentCustomer::dispatch($palletReturn->fulfilmentCustomer);
        if($this->sendNotifications) {
            SendPalletReturnNotification::run($palletReturn);
        }
        PalletReturnRecordSearch::dispatch($palletReturn);
        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        if ($this->asAction) {
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
        $this->sendNotifications = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, bool $sendNotification=false): PalletReturn
    {
        $this->asAction          = true;
        $this->sendNotifications = $sendNotification;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, []);
        return $this->handle($palletReturn, []);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer      = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment        = $fulfilmentCustomer->fulfilment;
        $this->sendNotifications = true;
        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($palletReturn, $this->validatedData);
    }
}
