<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferComponent;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\OfferComponentResource;
use App\Models\Marketing\OfferComponent;
use Lorisleiva\Actions\ActionRequest;

class UpdateOfferComponent
{
    use WithActionUpdate;

    public function handle(OfferComponent $offerComponent, array $modelData): OfferComponent
    {
        return $this->update($offerComponent, $modelData);
    }

//    public function authorize(ActionRequest $request): bool
//    {
//        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
//    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.offer_components', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferComponent $offerComponent, array $objectData): OfferComponent
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($offerComponent, $validatedData);
    }

    public function asController(OfferComponent $offerComponent, ActionRequest $request): OfferComponent
    {
        $request->validate();

        return $this->handle($offerComponent, $request->all());
    }


    public function jsonResponse(OfferComponent $offerComponent): OfferComponentResource
    {
        return new OfferComponentResource($offerComponent);
    }
}
