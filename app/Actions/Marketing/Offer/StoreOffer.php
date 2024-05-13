<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:03:50 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Offer;

use App\Models\Deals\Offer;
use App\Models\Deals\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOffer
{
    use AsAction;
    use WithAttributes;

    public function handle(OfferCampaign $offerCampaign, array $modelData): Offer
    {
        $modelData['shop_id'] = $offerCampaign->shop_id;
        /** @var \App\Models\Deals\Offer $offer */
        $offer = $offerCampaign->offers()->create($modelData);

        return $offer;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:offers', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required'],
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $modelData): Offer
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerCampaign, $validatedData);
    }
}
