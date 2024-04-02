<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateUniversalSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Events\BroadcastFulfilmentCustomerNotification;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitPalletDelivery extends OrgAction
{
    use WithActionUpdate;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        $modelData['submitted_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::SUBMITTED;

        foreach ($palletDelivery->pallets as $pallet) {
            $pallet->update([
                'reference' => GetSerialReference::run(
                    container: $palletDelivery->fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET
                ),
                'state'     => PalletStateEnum::SUBMITTED
            ]);
            $pallet->generateSlug();

            PalletHydrateUniversalSearch::run($pallet);
        }

        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);
        BroadcastFulfilmentCustomerNotification::dispatch(
            $palletDelivery->group,
            $palletDelivery,
            'Pallet Delivery Submitted',
            'Pallet Delivery has been submitted.'
        );

        return $this->update($palletDelivery, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
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
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery);
    }
}
