<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:03:50 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Offer;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\OfferResource;
use App\Models\Deals\Offer;
use Lorisleiva\Actions\ActionRequest;

class UpdateOffer
{
    use WithActionUpdate;

    public function handle(Offer $offer, array $modelData): Offer
    {
        return $this->update($offer, $modelData);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:offers', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required'],
        ];
    }

    public function action(Offer $offer, array $modelData): Offer
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offer, $validatedData);
    }

    public function asController(Offer $offer, ActionRequest $request): Offer
    {
        $request->validate();

        return $this->handle($offer, $request->all());
    }

    public function jsonResponse(Offer $offer): OfferResource
    {
        return new OfferResource($offer);
    }
}
