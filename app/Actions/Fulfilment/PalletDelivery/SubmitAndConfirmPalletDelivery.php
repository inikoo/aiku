<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jul 2024 13:51:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitAndConfirmPalletDelivery extends OrgAction
{
    use WithActionUpdate;

    /**
     * @var array|\ArrayAccess|mixed
     */
    private PalletDelivery $palletDelivery;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {


        $palletDelivery=SubmitPalletDelivery::make()->action($palletDelivery);

        return ConfirmPalletDelivery::make()->action($palletDelivery);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->palletDelivery->state != PalletDeliveryStateEnum::IN_PROCESS) {
            return false;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletDelivery $palletDelivery): JsonResource
    {
        return new PalletDeliveryResource($palletDelivery);
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->palletDelivery = $palletDelivery;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);
        return $this->handle($palletDelivery);
    }



}
