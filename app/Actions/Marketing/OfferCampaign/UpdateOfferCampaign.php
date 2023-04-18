<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferCampaign;

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
            'name' => ['sometimes', 'required'],
            'code' => ['sometimes', 'required'],
            'data' => ['sometimes', 'required'],
        ];
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
