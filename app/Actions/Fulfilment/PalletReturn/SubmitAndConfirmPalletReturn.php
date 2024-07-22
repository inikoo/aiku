<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jul 2024 13:51:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitAndConfirmPalletReturn extends OrgAction
{
    use WithActionUpdate;

    /**
     * @var array|\ArrayAccess|mixed
     */
    private PalletReturn $palletReturn;

    public function handle(PalletReturn $palletReturn): PalletReturn
    {


        $palletReturn=SubmitPalletReturn::make()->action($palletReturn);

        return ConfirmPalletReturn::make()->action($palletReturn);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->palletReturn->state != PalletReturnStateEnum::IN_PROCESS) {
            return false;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletReturn $palletReturn): JsonResource
    {
        return new PalletReturnResource($palletReturn);
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer,PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->palletReturn = $palletReturn;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);
        return $this->handle($palletReturn);
    }



}
