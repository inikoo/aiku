<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePallets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePallets;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitPalletDelivery extends OrgAction
{
    use WithActionUpdate;

    /**
     * @var array|\ArrayAccess|mixed
     */
    private PalletDelivery $palletDelivery;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        $modelData['submitted_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::SUBMITTED;

        $numberPallets       = $palletDelivery->pallets()->count();
        $numberStoredPallets = $palletDelivery->fulfilmentCustomer->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();

        $palletLimits = $palletDelivery->fulfilmentCustomer?->rentalAgreement?->pallets_limit ?? 0;
        $totalPallets = $numberPallets + $numberStoredPallets;

        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);

        $palletDelivery = $this->update($palletDelivery, $modelData);

        if($totalPallets < $palletLimits && !(request()->user() instanceof WebUser)) {
            $palletDelivery = ConfirmPalletDelivery::run($palletDelivery);
        }

        SendPalletDeliveryNotification::dispatch($palletDelivery);

        FulfilmentCustomerHydratePallets::dispatch($palletDelivery->fulfilmentCustomer);
        FulfilmentHydratePallets::dispatch($palletDelivery->fulfilment);
        OrganisationHydratePallets::dispatch($palletDelivery->organisation);
        WarehouseHydratePallets::dispatch($palletDelivery->warehouse);

        return $palletDelivery;
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

    public function action(PalletDelivery $palletDelivery): PalletDelivery
    {
        $this->palletDelivery = $palletDelivery;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, []);
        return $this->handle($palletDelivery);
    }


}
