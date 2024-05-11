<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:13 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferComponent;

use App\Models\Deals\OfferCampaign;
use App\Models\Deals\OfferComponent;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOfferComponent
{
    use AsAction;
    use WithAttributes;

    public function handle(OfferCampaign $offerCampaign, array $modelData): OfferComponent
    {
        /** @var OfferComponent */
        return $offerCampaign->offerComponent()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:offer_components', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $modelData): OfferComponent
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerCampaign, $validatedData);
    }
}
