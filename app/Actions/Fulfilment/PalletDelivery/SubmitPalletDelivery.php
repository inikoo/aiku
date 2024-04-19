<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateUniversalSearch;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePallets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePallets;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
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

        foreach ($palletDelivery->pallets as $pallet) {
            UpdatePallet::run($pallet, [
                'reference' => GetSerialReference::run(
                    container: $palletDelivery->fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET
                ),
                'state'  => PalletStateEnum::SUBMITTED,
                'status' => PalletStatusEnum::RECEIVING
            ]);
            $pallet->generateSlug();

            PalletHydrateUniversalSearch::run($pallet);
        }

        $numberPallets       = $palletDelivery->pallets()->count();
        $numberStoredPallets = $palletDelivery->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();

        $palletLimits = $palletDelivery->fulfilmentCustomer->rentalAgreement->pallets_limit;
        $totalPallets = $numberPallets + $numberStoredPallets;

        if($totalPallets < $palletLimits) {
            ConfirmPalletDelivery::run($palletDelivery);
        }

        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);

        $palletDelivery = $this->update($palletDelivery, $modelData);

        SendPalletDeliveryNotification::run($palletDelivery);

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

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
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
