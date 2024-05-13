<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:04:13 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferComponent;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\OfferComponentResource;
use App\Models\Deals\OfferComponent;
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
            'code' => ['required', 'unique:offer_components', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'data' => ['sometimes', 'required']
        ];
    }

    public function action(OfferComponent $offerComponent, array $modelData): OfferComponent
    {
        $this->setRawAttributes($modelData);
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
