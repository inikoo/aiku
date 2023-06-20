<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Offer;

use App\Models\Market\Offer;
use App\Models\Market\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOffer
{
    use AsAction;
    use WithAttributes;

    public function handle(OfferCampaign $offerCampaign, array $modelData): Offer
    {
        $modelData['shop_id'] = $offerCampaign->shop_id;
        /** @var Offer $offer */
        $offer = $offerCampaign->offers()->create($modelData);

        return $offer;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.offers', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required'],
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $objectData): Offer
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerCampaign, $validatedData);
    }
}
