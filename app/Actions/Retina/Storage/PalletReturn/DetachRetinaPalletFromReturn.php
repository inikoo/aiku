<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 23:14:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\AutoAssignServicesToPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\Notifications\SendPalletReturnNotification;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class DetachRetinaPalletFromReturn extends RetinaAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(PalletReturn $palletReturn, Pallet $pallet): bool
    {
        $this->update($pallet, ['pallet_return_id' => null,
            'status'                               => PalletStatusEnum::STORING,
            'state'                                => PalletStateEnum::STORING
        ]);

        $palletReturn->pallets()->detach([$pallet->id]);
        AutoAssignServicesToPalletReturn::run($palletReturn, $pallet);

        SendPalletReturnNotification::run($palletReturn);

        PalletReturnHydratePallets::dispatch($palletReturn);
        PalletReturnHydrateTransactions::dispatch($palletReturn);

        return true;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, Pallet $pallet, ActionRequest $request): bool
    {
        $this->initialisation($request);

        return $this->handle($palletReturn, $pallet);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
