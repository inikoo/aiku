<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class DeletePalletFromReturn extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(PalletReturn $palletReturn, Pallet $pallet): bool
    {
        $this->update($pallet, ['pallet_return_id' => null,
            'status'                               => PalletStatusEnum::STORING,
            'state'                                => PalletStateEnum::STORING]);
        $palletReturn->pallets()->detach([$pallet->id]);

        HydrateFulfilmentCustomer::dispatch($palletReturn->fulfilmentCustomer);
        SendPalletReturnNotification::run($palletReturn);

        return true;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, Pallet $pallet, ActionRequest $request): bool
    {
        $this->pallet = $pallet;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($palletReturn, $pallet);
    }

    public function fromRetina(PalletReturn $palletReturn, Pallet $pallet, ActionRequest $request): bool
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->pallet       = $pallet;
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($palletReturn, $pallet);
    }

    public function action(Pallet $pallet, array $modelData, int $hydratorsDelay = 0): bool
    {
        $this->pallet         = $pallet;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($pallet->fulfilment, $modelData);

        return $this->handle($pallet->palletReturn, $pallet);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
