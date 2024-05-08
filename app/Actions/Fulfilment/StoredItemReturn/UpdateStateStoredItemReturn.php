<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\StoredItemReturnResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class UpdateStateStoredItemReturn extends OrgAction
{
    use WithActionUpdate;


    public function handle(StoredItemReturn $storedItemReturn, string $state, array $modelData = []): StoredItemReturn
    {
        if (!request()->routeIs('retina.*') && $state == PalletReturnStateEnum::SUBMITTED->value) {
            $state = PalletReturnStateEnum::CONFIRMED->value;
        }

        $modelData[$state.'_at'] = now();
        $modelData['state']      = $state;

        HydrateFulfilmentCustomer::dispatch($storedItemReturn->fulfilmentCustomer);

        return $this->update($storedItemReturn, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(StoredItemReturn $storedItemReturn): JsonResource
    {
        return new StoredItemReturnResource($storedItemReturn);
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, StoredItemReturn $storedItemReturn, string $state, ActionRequest $request): StoredItemReturn
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($storedItemReturn, $state, $this->validatedData);
    }
}
