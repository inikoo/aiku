<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\OfferCampaign;

use App\Models\Market\OfferCampaign;
use App\Models\Market\Shop;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOfferCampaign
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop $shop, array $modelData): OfferCampaign
    {
        /** @var OfferCampaign $offerCampaign */
        $offerCampaign = $shop->offerCampaigns()->create($modelData);

        return $offerCampaign;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.offer_campaigns', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(Shop $shop, array $objectData): OfferCampaign
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
