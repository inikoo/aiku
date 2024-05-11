<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:01 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferCampaign;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\OfferCampaignResource;
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
            'code' => ['required', 'unique:offer_campaigns', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $modelData): OfferCampaign
    {
        $this->setRawAttributes($modelData);
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
