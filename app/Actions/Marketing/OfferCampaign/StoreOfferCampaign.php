<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:01 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferCampaign;

use App\Models\Catalogue\Shop;
use App\Models\Marketing\OfferCampaign;
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
            'code' => ['required', 'unique:offer_campaigns', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(Shop $shop, array $modelData): OfferCampaign
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
