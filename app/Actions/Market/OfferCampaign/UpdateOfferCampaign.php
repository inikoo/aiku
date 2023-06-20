<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\OfferCampaign;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\OfferCampaignResource;
use App\Models\Marketing\OfferCampaign;
use Lorisleiva\Actions\ActionRequest;

class UpdateOfferCampaign
{
    use WithActionUpdate;

    public function handle(OfferCampaign $offerCampaign, array $modelData): OfferCampaign
    {
        return $this->update($offerCampaign, $modelData);
    }

//    public function authorize(ActionRequest $request): bool
//    {
//        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
//    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.offer_campaigns', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $objectData): OfferCampaign
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerCampaign, $validatedData);
    }

    public function asController(OfferCampaign $offerCampaign, ActionRequest $request): OfferCampaign
    {
        $request->validate();

        return $this->handle($offerCampaign, $request->all());
    }


    public function jsonResponse(OfferCampaign $offerCampaign): OfferCampaignResource
    {
        return new OfferCampaignResource($offerCampaign);
    }
}
