<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Offer;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\OfferResource;
use App\Models\Marketing\Offer;
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
            'name' => ['sometimes', 'required'],
            'code' => ['sometimes', 'required'],
            'data' => ['sometimes', 'required'],
        ];
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
