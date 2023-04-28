<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferComponent;

use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\OfferComponent;
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
            'code' => ['required', 'unique:tenant.offer_components', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferCampaign $offerCampaign, array $objectData): OfferComponent
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerCampaign, $validatedData);
    }
}
